<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Services\AuditLogger;
use App\Services\ProcurementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FarmerPaymentController extends Controller
{
    public function __construct(private ProcurementService $procurement) {}

    public function index(Request $request): View
    {
        $baseQuery = PurchasePayment::query();

        // Summary KPI aggregates
        $totalDisbursed = (clone $baseQuery)->active()->selectRaw('COALESCE(SUM(CASE WHEN total_payout > 0 THEN total_payout ELSE amount END), 0) as total')->value('total') ?? '0.00';
        $totalBonusPaid = (clone $baseQuery)->active()->sum('bonus_amount') ?? '0.00';
        $activeCount = (clone $baseQuery)->active()->count();
        $bonusCount = (clone $baseQuery)->active()->where('bonus_amount', '>', 0)->count();
        $reversedCount = (clone $baseQuery)->reversed()->count();
        $totalCount = (clone $baseQuery)->count();

        $todayDisbursed = (clone $baseQuery)->active()->whereDate('payment_date', today())->selectRaw('COALESCE(SUM(CASE WHEN total_payout > 0 THEN total_payout ELSE amount END), 0) as total')->value('total') ?? '0.00';
        $todayCount = (clone $baseQuery)->active()->whereDate('payment_date', today())->count();

        $pendingPurchaseDues = Purchase::where('status', Purchase::STATUS_FINALIZED)->sum('balance_due') ?? '0.00';
        $pendingPurchasesCount = Purchase::where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0)->count();

        $payments = PurchasePayment::with(['farmer', 'purchase.crop', 'creator', 'reverser'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('voucher_number', 'like', $term)
                        ->orWhere('transaction_reference', 'like', $term)
                        ->orWhereHas('farmer', fn ($f) => $f->where('name', 'like', $term)->orWhere('farmer_id', 'like', $term)->orWhere('phone', 'like', $term))
                        ->orWhereHas('purchase', fn ($p) => $p->where('purchase_number', 'like', $term));
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'active') {
                    $q->active();
                } elseif ($request->status === 'reversed') {
                    $q->reversed();
                }
            })
            ->when($request->filled('bonus'), function ($q) use ($request) {
                if ($request->bonus === 'with_bonus') {
                    $q->where('bonus_amount', '>', 0);
                } elseif ($request->bonus === 'none') {
                    $q->where(function ($b) {
                        $b->whereNull('bonus_amount')->orWhere('bonus_amount', '<=', 0);
                    });
                }
            })
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('farmer_id'), fn ($q) => $q->where('farmer_id', $request->farmer_id))
            ->when($request->filled('from'), fn ($q) => $q->where('payment_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('payment_date', '<=', Carbon::parse($request->to)->endOfDay()))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('procurement.payments.index', [
            'payments' => $payments,
            'methods' => PurchasePayment::METHODS,
            'farmers' => Farmer::where('is_active', true)->orderBy('name')->get(),
            'kpis' => [
                'total_disbursed' => $totalDisbursed,
                'total_bonus_paid' => $totalBonusPaid,
                'active_count' => $activeCount,
                'bonus_count' => $bonusCount,
                'reversed_count' => $reversedCount,
                'total_count' => $totalCount,
                'today_disbursed' => $todayDisbursed,
                'today_count' => $todayCount,
                'pending_purchase_dues' => $pendingPurchaseDues,
                'pending_purchases_count' => $pendingPurchasesCount,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $selectedPurchase = null;
        if ($request->filled('purchase_id')) {
            $selectedPurchase = Purchase::with('farmer')->find($request->purchase_id);
        } elseif ($request->filled('farmer_id')) {
            $selectedPurchase = Purchase::with('farmer')
                ->where('farmer_id', $request->farmer_id)
                ->where('status', Purchase::STATUS_FINALIZED)
                ->where('balance_due', '>', 0)
                ->orderBy('purchase_date')
                ->first();
        }

        $pendingPurchases = Purchase::with(['farmer', 'crop'])
            ->where('status', Purchase::STATUS_FINALIZED)
            ->where('balance_due', '>', 0)
            ->orderBy('purchase_date')
            ->get();

        return view('procurement.payments.form', [
            'payment' => new PurchasePayment([
                'payment_date' => now(),
                'payment_method' => 'cash',
                'amount' => $selectedPurchase ? $selectedPurchase->balance_due : 0.00,
            ]),
            'selectedPurchase' => $selectedPurchase,
            'pendingPurchases' => $pendingPurchases,
            'methods' => PurchasePayment::METHODS,
            'defaultBonusRate' => $selectedPurchase?->farmer ? $selectedPurchase->farmer->effective_bonus_rate : setting('procurement.delayed_bonus_rate', '1.00'),
            'defaultBonusMode' => $selectedPurchase?->farmer ? $selectedPurchase->farmer->effective_bonus_mode : setting('procurement.delayed_bonus_mode', 'monthly'),
            'bonusEnabled' => setting('procurement.delayed_bonus_enabled', '1'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'purchase_id' => ['required', 'exists:purchases,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'apply_bonus' => ['nullable', 'boolean'],
            'bonus_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bonus_amount' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'bonus_calculation_mode' => ['nullable', 'in:monthly,flat,custom'],
            'payment_method' => ['required', 'in:cash,bank_transfer,upi,cheque'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $purchase = Purchase::with('farmer')->findOrFail($data['purchase_id']);
        if (! $purchase->isFinalized()) {
            return back()->withInput()->with('error', 'Payments can only be recorded against finalized purchases.');
        }

        $purchaseDate = Carbon::parse($purchase->purchase_date)->startOfDay();
        $today = now()->startOfDay();
        $daysDelayed = (int) max(0, $purchaseDate->diffInDays($today, false));

        $farmerBonusEnabled = $purchase->farmer ? $purchase->farmer->isDelayedBonusEnabled() : (setting('procurement.delayed_bonus_enabled', '1') == '1');
        $applyBonus = $request->has('apply_bonus_present') ? $request->boolean('apply_bonus') : $farmerBonusEnabled;
        $bonusMode = $data['bonus_calculation_mode'] ?? ($purchase->farmer?->effective_bonus_mode ?? setting('procurement.delayed_bonus_mode', 'monthly'));

        if (! $applyBonus) {
            $bonusPct = '0.00';
            $bonusAmt = '0.00';
        } else {
            $bonusPct = $data['bonus_percentage'] ?? ($purchase->farmer?->effective_bonus_rate ?? '0.00');
            if (array_key_exists('bonus_amount', $data) && $data['bonus_amount'] !== null && $data['bonus_amount'] !== '') {
                $bonusAmt = $data['bonus_amount'];
            } elseif (bccomp((string) $bonusPct, '0.00', 2) > 0 && $daysDelayed > 0) {
                if ($bonusMode === 'monthly') {
                    $factor = bcdiv(bcmul((string) $bonusPct, (string) $daysDelayed, 6), '3000', 6);
                    $bonusAmt = bc_round(bcmul((string) $data['amount'], $factor, 6), 2);
                } elseif ($bonusMode === 'flat') {
                    $factor = bcdiv((string) $bonusPct, '100', 6);
                    $bonusAmt = bc_round(bcmul((string) $data['amount'], $factor, 6), 2);
                } else {
                    $bonusAmt = '0.00';
                }
            } else {
                $bonusAmt = '0.00';
            }
        }

        $payment = $this->procurement->recordPayment(
            $purchase,
            $data['amount'],
            $data['payment_method'],
            $data['transaction_reference'] ?? null,
            $data['notes'] ?? null,
            $request->user()->id,
            $bonusPct,
            $bonusAmt,
            $bonusMode,
            $daysDelayed
        );

        $msg = "Payment voucher {$payment->voucher_number} recorded. Remaining balance: " . money($purchase->fresh()->balance_due);
        if (bccomp((string) $payment->bonus_amount, '0.00', 2) > 0) {
            $msg .= " (Includes " . money($payment->bonus_amount) . " delayed payment bonus. Total payout: " . money($payment->total_payout) . ")";
        }

        return redirect()->route('farmer-payments.show', $payment)->with('success', $msg);
    }

    public function show(PurchasePayment $payment): View
    {
        $payment->load(['farmer', 'purchase.crop', 'creator', 'reverser']);

        return view('procurement.payments.show', compact('payment'));
    }

    public function print(PurchasePayment $payment): View
    {
        $payment->load(['farmer', 'purchase.crop', 'creator']);

        return view('procurement.payments.print', compact('payment'));
    }

    public function reverse(Request $request, PurchasePayment $payment): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        if ($payment->isReversed()) {
            return back()->with('error', 'This payment voucher is already reversed.');
        }

        DB::transaction(function () use ($payment, $request) {
            $purchase = $payment->purchase;

            // Restore balance on purchase
            $newPaid = bcsub((string) $purchase->paid_amount, (string) $payment->amount, 2);
            $newBalance = bcadd((string) $purchase->balance_due, (string) $payment->amount, 2);

            $purchase->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
            ]);

            $payment->update([
                'reversed_at' => now(),
                'reversed_by' => $request->user()->id,
                'reversal_reason' => $request->reason,
            ]);

            AuditLogger::log('farmer_payment.reversed', $payment, [], ['reason' => $request->reason], "Reversed payment voucher {$payment->voucher_number}", $request->user()->id);
        });

        return redirect()->route('farmer-payments.show', $payment)->with('success', "Payment voucher {$payment->voucher_number} reversed.");
    }
}
