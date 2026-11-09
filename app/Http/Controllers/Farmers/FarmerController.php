<?php

namespace App\Http\Controllers\Farmers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Services\AuditLogger;
use App\Services\DocumentNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FarmerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Farmer::query()
            ->withCount([
                'purchases as finalized_purchases_count' => fn ($q) => $q->where('status', Purchase::STATUS_FINALIZED),
            ])
            ->withSum(
                ['purchases as finalized_qtl_sum' => fn ($q) => $q->where('status', Purchase::STATUS_FINALIZED)],
                'net_weight_qtl'
            )
            ->withSum(
                ['purchases as purchases_due_sum' => fn ($q) => $q->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0)],
                'balance_due'
            )
            ->search($request->q)
            ->when($request->filled('village'), fn ($q) => $q->where('village', $request->village))
            ->when($request->filled('block'), fn ($q) => $q->where('block', $request->block))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->input('due') === 'pending', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('opening_balance', '>', 0)
                        ->orWhereHas('purchases', fn ($pq) => $pq->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0));
                });
            })
            ->when($request->input('due') === 'settled', function ($q) {
                $q->where('opening_balance', '<=', 0)
                    ->whereDoesntHave('purchases', fn ($pq) => $pq->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0));
            })
            ->when($request->input('bonus') === 'custom', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('delayed_bonus_enabled', false)
                        ->orWhereNotNull('delayed_bonus_rate')
                        ->orWhereNotNull('delayed_bonus_mode');
                });
            });

        $farmers = $query->orderBy('name')->paginate(20)->withQueryString();

        $villages = Farmer::whereNotNull('village')->where('village', '!=', '')->distinct()->orderBy('village')->pluck('village');
        $blocks = Farmer::whereNotNull('block')->where('block', '!=', '')->distinct()->orderBy('block')->pluck('block');

        $openingDueTotal = (float) (Farmer::where('opening_balance', '>', 0)->sum('opening_balance') ?? 0);
        $purchaseDueTotal = (float) (Purchase::where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0)->sum('balance_due') ?? 0);

        $summary = [
            'total' => Farmer::count(),
            'active' => Farmer::where('is_active', true)->count(),
            'inactive' => Farmer::where('is_active', false)->count(),
            'total_qtl' => (float) (Purchase::where('status', Purchase::STATUS_FINALIZED)->sum('net_weight_qtl') ?? 0),
            'total_slips' => Purchase::where('status', Purchase::STATUS_FINALIZED)->count(),
            'total_payable' => round($openingDueTotal + $purchaseDueTotal, 2),
            'farmers_with_due' => Farmer::where(function ($q) {
                $q->where('opening_balance', '>', 0)
                    ->orWhereHas('purchases', fn ($pq) => $pq->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0));
            })->count(),
            'villages_count' => $villages->count(),
            'custom_bonus_count' => Farmer::where(function ($q) {
                $q->where('delayed_bonus_enabled', false)
                    ->orWhereNotNull('delayed_bonus_rate')
                    ->orWhereNotNull('delayed_bonus_mode');
            })->count(),
        ];

        return view('farmers.index', compact('farmers', 'villages', 'blocks', 'summary'));
    }

    public function create(): View
    {
        return view('farmers.form', [
            'farmer' => new Farmer([
                'is_active' => true,
                'delayed_bonus_enabled' => true,
                'registration_date' => now()->toDateString(),
                'opening_balance' => 0.00,
                'state' => setting('business.state', ''),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $farmer = DB::transaction(function () use ($data, $request) {
            $farmerId = DocumentNumber::next('farmer');

            $payload = [
                ...$data,
                'farmer_id' => $farmerId,
                'is_active' => $request->boolean('is_active'),
                'created_by' => $request->user()->id,
            ];

            // Only users with farmers.bank permission can store bank/ID details
            if (! Gate::allows('farmers.bank')) {
                unset($payload['bank_name'], $payload['bank_branch'], $payload['bank_account_number'], $payload['bank_ifsc'], $payload['id_type'], $payload['id_number']);
            }

            // Only Admin can configure farmer-level delayed payment bonus settings
            if ($request->user()->isAdmin()) {
                if ($request->has('farmer_bonus_section_present')) {
                    $payload['delayed_bonus_enabled'] = $request->boolean('delayed_bonus_enabled');
                    $payload['delayed_bonus_rate'] = isset($data['delayed_bonus_rate']) && $data['delayed_bonus_rate'] !== ''
                        ? bc_round((string) $data['delayed_bonus_rate'], 2)
                        : null;
                    $payload['delayed_bonus_mode'] = ! empty($data['delayed_bonus_mode']) ? $data['delayed_bonus_mode'] : null;
                }
            } else {
                unset($payload['delayed_bonus_enabled'], $payload['delayed_bonus_rate'], $payload['delayed_bonus_mode']);
            }

            $farmer = Farmer::create($payload);

            AuditLogger::log('farmer.created', $farmer, [], $farmer->toArray(), "Registered farmer {$farmer->name} ({$farmer->farmer_id})");

            return $farmer;
        });

        return redirect()->route('farmers.show', $farmer)->with('success', "Farmer '{$farmer->name}' registered successfully with ID {$farmer->farmer_id}.");
    }

    public function show(Farmer $farmer): View
    {
        $farmer->load('creator');

        $purchases = $farmer->purchases()
            ->with(['crop', 'cropVariety', 'warehouse'])
            ->latest('purchase_date')
            ->latest('id')
            ->get();

        $payments = $farmer->payments()
            ->with(['purchase', 'creator', 'reverser'])
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $finalizedPurchases = $purchases->where('status', Purchase::STATUS_FINALIZED);
        $validPayments = $payments->whereNull('reversed_at');

        $purchasesDue = (float) $finalizedPurchases->where('balance_due', '>', 0)->sum('balance_due');
        $totalPaidPrincipal = (float) $validPayments->sum('amount');
        $totalBonusPaid = (float) $validPayments->sum('bonus_amount');

        $ledgerStats = [
            'total_qtl' => (float) $finalizedPurchases->sum('net_weight_qtl'),
            'purchases_count' => $finalizedPurchases->count(),
            'gross_purchases' => (float) $finalizedPurchases->sum('net_payable_amount'),
            'purchases_due' => $purchasesDue,
            'net_payable' => round(((float) $farmer->opening_balance) + $purchasesDue, 2),
            'total_paid_principal' => $totalPaidPrincipal,
            'total_bonus_paid' => $totalBonusPaid,
            'total_payout' => round($totalPaidPrincipal + $totalBonusPaid, 2),
        ];

        return view('farmers.show', compact('farmer', 'purchases', 'payments', 'ledgerStats'));
    }

    public function edit(Farmer $farmer): View
    {
        return view('farmers.form', compact('farmer'));
    }

    public function update(Request $request, Farmer $farmer): RedirectResponse
    {
        $data = $this->validated($request, $farmer);

        DB::transaction(function () use ($data, $farmer, $request) {
            $original = $farmer->getOriginal();

            $payload = [
                ...$data,
                'is_active' => $request->boolean('is_active'),
            ];

            // Protect bank details if the user lacks the permission
            if (! Gate::allows('farmers.bank')) {
                unset($payload['bank_name'], $payload['bank_branch'], $payload['bank_account_number'], $payload['bank_ifsc'], $payload['id_type'], $payload['id_number']);
            }

            // Only Admin can configure farmer-level delayed payment bonus settings
            if ($request->user()->isAdmin()) {
                if ($request->has('farmer_bonus_section_present')) {
                    $payload['delayed_bonus_enabled'] = $request->boolean('delayed_bonus_enabled');
                    $payload['delayed_bonus_rate'] = isset($data['delayed_bonus_rate']) && $data['delayed_bonus_rate'] !== ''
                        ? bc_round((string) $data['delayed_bonus_rate'], 2)
                        : null;
                    $payload['delayed_bonus_mode'] = ! empty($data['delayed_bonus_mode']) ? $data['delayed_bonus_mode'] : null;
                }
            } else {
                unset($payload['delayed_bonus_enabled'], $payload['delayed_bonus_rate'], $payload['delayed_bonus_mode']);
            }

            $farmer->update($payload);

            AuditLogger::changes('farmer.updated', $farmer, $original, "Updated farmer {$farmer->name}");
        });

        return redirect()->route('farmers.show', $farmer)->with('success', "Farmer '{$farmer->name}' details updated.");
    }

    public function toggle(Farmer $farmer): RedirectResponse
    {
        $farmer->update(['is_active' => ! $farmer->is_active]);
        AuditLogger::log($farmer->is_active ? 'farmer.activated' : 'farmer.deactivated', $farmer);

        return back()->with('success', "Farmer '{$farmer->name}' " . ($farmer->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function print(Farmer $farmer): View
    {
        return view('farmers.print', compact('farmer'));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(Gate::allows('farmers.export'), 403, 'You do not have permission to export farmer records.');

        AuditLogger::log('farmer.exported', null, [], [], 'Exported farmers CSV');

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="farmers_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Farmer ID', 'Full Name', 'Mobile', 'Address', 'Village', 'Panchayat',
                'Block', 'District', 'State', 'Opening Balance', 'Purchase Dues', 'Net Payable', 'Status', 'Registration Date',
            ]);

            Farmer::query()
                ->withSum(
                    ['purchases as purchases_due_sum' => fn ($q) => $q->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0)],
                    'balance_due'
                )
                ->search($request->q)
                ->when($request->filled('village'), fn ($q) => $q->where('village', $request->village))
                ->when($request->filled('block'), fn ($q) => $q->where('block', $request->block))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->orderBy('farmer_id')
                ->chunk(200, function ($farmers) use ($handle) {
                    foreach ($farmers as $f) {
                        $purchaseDue = (float) ($f->purchases_due_sum ?? 0);
                        $netPayable = round(((float) $f->opening_balance) + $purchaseDue, 2);
                        fputcsv($handle, [
                            $f->farmer_id,
                            $f->name,
                            $f->mobile ?: '',
                            $f->address ?: '',
                            $f->village ?: '',
                            $f->panchayat ?: '',
                            $f->block ?: '',
                            $f->district ?: '',
                            $f->state ?: '',
                            $f->opening_balance,
                            number_format($purchaseDue, 2, '.', ''),
                            number_format($netPayable, 2, '.', ''),
                            $f->is_active ? 'Active' : 'Inactive',
                            $f->registration_date ? $f->registration_date->format('Y-m-d') : '',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }

    private function validated(Request $request, ?Farmer $farmer = null): array
    {
        if ($request->filled('bank_ifsc')) {
            $request->merge([
                'bank_ifsc' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('bank_ifsc'))),
            ]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:100'],
            'panchayat' => ['nullable', 'string', 'max:100'],
            'block' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_branch' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i'],
            'id_type' => ['nullable', 'string', 'max:30'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'registration_date' => ['nullable', 'date'],
            'opening_balance' => ['nullable', 'numeric'],
            'delayed_bonus_enabled' => ['nullable', 'boolean'],
            'delayed_bonus_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delayed_bonus_mode' => ['nullable', 'in:monthly,flat,custom'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'bank_ifsc.regex' => 'Enter a valid 11-character IFSC code (e.g. SBIN0001234).',
        ]);
    }
}
