<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Models\Warehouse;
use App\Services\ProcurementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(private ProcurementService $procurement) {}

    public function index(Request $request): View
    {
        $purchases = Purchase::with(['farmer', 'crop', 'cropVariety', 'warehouse', 'creator'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->q).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('purchase_number', 'like', $term)
                        ->orWhere('vehicle_number', 'like', $term)
                        ->orWhereHas('farmer', fn ($f) => $f->where('name', 'like', $term)
                            ->orWhere('farmer_id', 'like', $term)
                            ->orWhere('mobile', 'like', $term)
                            ->orWhere('village', 'like', $term));
                });
            })
            ->when($request->filled('crop_id'), fn ($q) => $q->where('crop_id', $request->crop_id))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->input('payment') === 'due', fn ($q) => $q->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0))
            ->when($request->input('payment') === 'paid', fn ($q) => $q->where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '<=', 0))
            ->when($request->filled('from'), fn ($q) => $q->where('purchase_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('purchase_date', '<=', Carbon::parse($request->to)->endOfDay()))
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $finalizedAgg = Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(net_weight_qtl), 0) as qtl, COALESCE(SUM(net_payable_amount), 0) as val')
            ->first();

        $todayAgg = Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->whereBetween('purchase_date', [$todayStart, $todayEnd])
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(net_weight_qtl), 0) as qtl, COALESCE(SUM(net_payable_amount), 0) as val')
            ->first();

        $unpaidAgg = Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->where('balance_due', '>', 0)
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(balance_due), 0) as due')
            ->first();

        $summary = [
            'total_count' => Purchase::count(),
            'finalized_count' => (int) ($finalizedAgg->cnt ?? 0),
            'finalized_qtl' => (float) ($finalizedAgg->qtl ?? 0),
            'finalized_value' => (float) ($finalizedAgg->val ?? 0),
            'today_count' => (int) ($todayAgg->cnt ?? 0),
            'today_qtl' => (float) ($todayAgg->qtl ?? 0),
            'today_value' => (float) ($todayAgg->val ?? 0),
            'unpaid_count' => (int) ($unpaidAgg->cnt ?? 0),
            'unpaid_due' => (float) ($unpaidAgg->due ?? 0),
            'draft_count' => Purchase::where('status', Purchase::STATUS_DRAFT)->count(),
            'reversed_count' => Purchase::where('status', Purchase::STATUS_REVERSED)->count(),
        ];

        return view('procurement.purchases.index', [
            'purchases' => $purchases,
            'crops' => Crop::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'summary' => $summary,
        ]);
    }

    public function create(Request $request): View
    {
        return view('procurement.purchases.form', [
            'purchase' => new Purchase([
                'purchase_date' => now(),
                'status' => 'draft',
                'quality_grade' => 'Grade A',
                'tare_weight_kg' => 0.000,
                'deductions_amount' => 0.00,
                'additions_amount' => 0.00,
            ]),
            'farmers' => Farmer::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::with('varieties')->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'selectedFarmerId' => $request->query('farmer_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $action = $request->input('action');
        $finalize = in_array($action, ['finalize', 'finalize_sell']);

        $purchase = $this->procurement->create($data, $request->user()->id, $finalize);

        if ($action === 'finalize_sell') {
            return redirect()->route('sales-invoices.create', ['purchase_id' => $purchase->id])
                ->with('success', "Purchase {$purchase->purchase_number} finalized! Select customer to complete direct sale dispatch.");
        }

        $msg = $finalize
            ? "Purchase {$purchase->purchase_number} finalized and stock posted to inventory."
            : "Purchase draft {$purchase->purchase_number} saved.";

        return redirect()->route('purchases.show', $purchase)->with('success', $msg);
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['farmer', 'crop', 'cropVariety', 'warehouse', 'creator', 'reverser', 'payments.creator']);

        return view('procurement.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        abort_unless($purchase->isDraft(), 403, 'Only draft purchases can be edited.');

        return view('procurement.purchases.form', [
            'purchase' => $purchase,
            'farmers' => Farmer::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::with('varieties')->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'selectedFarmerId' => $purchase->farmer_id,
        ]);
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        abort_unless($purchase->isDraft(), 403, 'Only draft purchases can be edited.');

        $data = $this->validated($request);
        $calc = ProcurementService::calculate(
            $data['gross_weight_kg'],
            $data['tare_weight_kg'] ?? '0.000',
            $data['rate_per_qtl'],
            $data['deductions_amount'] ?? '0.00',
            $data['additions_amount'] ?? '0.00'
        );

        $purchase->update([
            'purchase_date' => $data['purchase_date'] ?? $purchase->purchase_date,
            'farmer_id' => $data['farmer_id'],
            'crop_id' => $data['crop_id'],
            'crop_variety_id' => $data['crop_variety_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'],
            'gross_weight_kg' => $calc['gross_weight_kg'],
            'tare_weight_kg' => $calc['tare_weight_kg'],
            'net_weight_kg' => $calc['net_weight_kg'],
            'net_weight_qtl' => $calc['net_weight_qtl'],
            'moisture_pct' => ! empty($data['moisture_pct']) ? bc_round((string) $data['moisture_pct'], 2) : null,
            'quality_grade' => $data['quality_grade'] ?? 'Standard',
            'rate_per_qtl' => $calc['rate_per_qtl'],
            'base_amount' => $calc['base_amount'],
            'deductions_amount' => $calc['deductions_amount'],
            'deduction_reason' => $data['deduction_reason'] ?? null,
            'additions_amount' => $calc['additions_amount'],
            'net_payable_amount' => $calc['net_payable_amount'],
            'balance_due' => bcsub((string) $calc['net_payable_amount'], (string) $purchase->paid_amount, 2),
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'driver_name' => $data['driver_name'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        $action = $request->input('action');
        if (in_array($action, ['finalize', 'finalize_sell'])) {
            $this->procurement->finalize($purchase, $request->user()->id, $data['initial_paid_amount'] ?? '0.00');

            if ($action === 'finalize_sell') {
                return redirect()->route('sales-invoices.create', ['purchase_id' => $purchase->id])
                    ->with('success', "Purchase {$purchase->purchase_number} finalized! Select customer to complete direct sale dispatch.");
            }

            return redirect()->route('purchases.show', $purchase)->with('success', "Purchase {$purchase->purchase_number} finalized and stock posted to inventory.");
        }

        return redirect()->route('purchases.show', $purchase)->with('success', "Purchase draft updated.");
    }

    public function finalize(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->procurement->finalize(
            $purchase,
            $request->user()->id,
            $request->input('payment_amount', '0.00'),
            $request->input('payment_method', 'cash'),
            $request->input('payment_reference')
        );

        return redirect()->route('purchases.show', $purchase)->with('success', "Purchase {$purchase->purchase_number} finalized. Stock ledger updated.");
    }

    public function reverse(Request $request, Purchase $purchase): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        $this->procurement->reverse($purchase, $request->reason, $request->user()->id);

        return redirect()->route('purchases.show', $purchase)->with('success', "Purchase {$purchase->purchase_number} reversed. Stock and records restored.");
    }

    public function receipt(Purchase $purchase): View
    {
        $purchase->load(['farmer', 'crop', 'cropVariety', 'warehouse', 'creator', 'payments']);

        return view('procurement.purchases.receipt', compact('purchase'));
    }

    public function pdf(Purchase $purchase): Response
    {
        $purchase->load(['farmer', 'crop', 'cropVariety', 'warehouse', 'creator', 'payments']);

        $pdf = Pdf::loadView('procurement.purchases.receipt', ['purchase' => $purchase, 'isPdf' => true])
            ->setPaper('a4', 'portrait');

        $filename = 'Purchase_Receipt_' . str_replace(['/', '\\'], '_', $purchase->purchase_number) . '.pdf';

        return $pdf->download($filename);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'purchase_date' => ['required', 'date'],
            'farmer_id' => ['required', 'exists:farmers,id'],
            'crop_id' => ['required', 'exists:crops,id'],
            'crop_variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'gross_weight_kg' => ['required', 'numeric', 'gt:tare_weight_kg', 'max:1000000'],
            'tare_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'rate_per_qtl' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'moisture_pct' => ['nullable', 'numeric', 'between:0,100'],
            'quality_grade' => ['nullable', 'string', 'max:30'],
            'deductions_amount' => ['nullable', 'numeric', 'min:0'],
            'deduction_reason' => ['nullable', 'string', 'max:150'],
            'additions_amount' => ['nullable', 'numeric', 'min:0'],
            'initial_paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'gross_weight_kg.gt' => 'Gross weight must be strictly greater than Tare weight.',
        ]);
    }
}
