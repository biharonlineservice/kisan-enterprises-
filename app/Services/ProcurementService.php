<?php

namespace App\Services;

use App\Models\Farmer;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ProcurementService
{
    /**
     * Compute decimal-safe weights and monetary calculations using bcmath.
     */
    public static function calculate(
        string|float $grossKg,
        string|float $tareKg,
        string|float $ratePerQtl,
        string|float $deductions = '0.00',
        string|float $additions = '0.00',
        string|float $paidAmount = '0.00'
    ): array {
        $gross = bc_round((string) $grossKg, 3);
        $tare = bc_round((string) $tareKg, 3);
        $rate = bc_round((string) $ratePerQtl, 2);
        $ded = bc_round((string) $deductions, 2);
        $add = bc_round((string) $additions, 2);
        $paid = bc_round((string) $paidAmount, 2);

        $netKg = bcsub($gross, $tare, 3);
        if (bccomp($netKg, '0', 3) <= 0) {
            throw new InvalidArgumentException('Net weight must be greater than zero (Gross weight must exceed Tare weight).');
        }

        // Net quantity in quintals = Net weight in kg / 100
        $netQtl = bcdiv($netKg, '100', 3);

        // Base amount = Net Qtl * Rate per Qtl
        $rawBase = bcmul($netQtl, $rate, 4);
        $base = bc_round($rawBase, 2);

        // Net payable = Base + Additions - Deductions
        $netPayable = bcsub(bcadd($base, $add, 2), $ded, 2);
        if (bccomp($netPayable, '0', 2) < 0) {
            $netPayable = '0.00';
        }

        // Balance due = Net payable - Paid amount
        $balanceDue = bcsub($netPayable, $paid, 2);

        return [
            'gross_weight_kg' => $gross,
            'tare_weight_kg' => $tare,
            'net_weight_kg' => $netKg,
            'net_weight_qtl' => $netQtl,
            'rate_per_qtl' => $rate,
            'base_amount' => $base,
            'deductions_amount' => $ded,
            'additions_amount' => $add,
            'net_payable_amount' => $netPayable,
            'paid_amount' => $paid,
            'balance_due' => $balanceDue,
        ];
    }

    /**
     * Create a purchase record (saved as draft by default, or finalized if requested).
     */
    public function create(array $data, int $userId, bool $finalize = false): Purchase
    {
        return DB::transaction(function () use ($data, $userId, $finalize) {
            $calc = self::calculate(
                $data['gross_weight_kg'],
                $data['tare_weight_kg'] ?? '0.000',
                $data['rate_per_qtl'],
                $data['deductions_amount'] ?? '0.00',
                $data['additions_amount'] ?? '0.00',
                $data['initial_paid_amount'] ?? '0.00'
            );

            $purchaseNumber = DocumentNumber::next('purchase');

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'purchase_date' => $data['purchase_date'] ?? now(),
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
                'paid_amount' => '0.00',
                'balance_due' => $calc['net_payable_amount'],
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'status' => Purchase::STATUS_DRAFT,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
            ]);

            AuditLogger::log('purchase.created', $purchase, [], $purchase->toArray(), "Created purchase draft {$purchase->purchase_number}", $userId);

            if ($finalize) {
                $this->finalize($purchase, $userId, $data['initial_paid_amount'] ?? '0.00', $data['payment_method'] ?? 'cash', $data['payment_reference'] ?? null);
            }

            return $purchase;
        });
    }

    /**
     * Finalize purchase: locks weights, posts stock inward to inventory ledger, records initial payment if any.
     */
    public function finalize(
        Purchase $purchase,
        int $userId,
        string|float $initialPayment = '0.00',
        string $paymentMethod = 'cash',
        ?string $paymentRef = null
    ): void {
        if (! $purchase->isDraft()) {
            throw new RuntimeException("Purchase {$purchase->purchase_number} is already {$purchase->status} and cannot be finalized again.");
        }

        DB::transaction(function () use ($purchase, $userId, $initialPayment, $paymentMethod, $paymentRef) {
            // 1. Post stock inward to InventoryTransaction ledger (signed quantity in kg and qtl)
            InventoryTransaction::create([
                'warehouse_id' => $purchase->warehouse_id,
                'crop_id' => $purchase->crop_id,
                'crop_variety_id' => $purchase->crop_variety_id,
                'transaction_date' => $purchase->purchase_date,
                'quantity_kg' => $purchase->net_weight_kg,
                'quantity_qtl' => $purchase->net_weight_qtl,
                'source_type' => 'purchase',
                'source_id' => $purchase->id,
                'reference_number' => $purchase->purchase_number,
                'direction' => 'in',
                'description' => "Procurement from {$purchase->farmer->name} ({$purchase->purchase_number})",
                'created_by' => $userId,
            ]);

            // 2. Record initial payment if provided
            $initialPaid = bc_round((string) $initialPayment, 2);
            if (bccomp($initialPaid, '0.00', 2) > 0) {
                $this->recordPayment($purchase, $initialPaid, $paymentMethod, $paymentRef, 'Initial payment at weighment', $userId);
            }

            // 3. Mark status as finalized
            $purchase->update([
                'status' => Purchase::STATUS_FINALIZED,
                'updated_by' => $userId,
            ]);

            AuditLogger::log('purchase.finalized', $purchase, ['status' => 'draft'], ['status' => 'finalized'], "Finalized purchase {$purchase->purchase_number}", $userId);
        });

        try {
            $settings = app(SettingsService::class);
            if ($settings->bool('whatsapp.enabled') && $settings->bool('whatsapp.auto_notify_purchase')) {
                $purchase->loadMissing(['farmer', 'crop']);
                if (! empty($purchase->farmer?->mobile)) {
                    $wa = app(WhatsAppService::class);
                    $wa->send($purchase->farmer->mobile, $wa->formatPurchaseMessage($purchase));
                }
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Record partial or full payment against a purchase.
     */
    public function recordPayment(
        Purchase $purchase,
        string|float $amount,
        string $method = 'cash',
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null,
        string|float $bonusPct = '0.00',
        string|float $bonusAmount = '0.00',
        string $bonusMode = 'monthly',
        int $daysDelayed = 0
    ): PurchasePayment {
        $amount = bc_round((string) $amount, 2);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $bonusPct = bc_round((string) $bonusPct, 2);
        $bonusAmount = bc_round((string) $bonusAmount, 2);
        $totalPayout = bcadd($amount, $bonusAmount, 2);

        return DB::transaction(function () use ($purchase, $amount, $method, $reference, $notes, $userId, $bonusPct, $bonusAmount, $totalPayout, $bonusMode, $daysDelayed) {
            $voucherNumber = DocumentNumber::next('farmer_payment');

            $payment = PurchasePayment::create([
                'voucher_number' => $voucherNumber,
                'purchase_id' => $purchase->id,
                'farmer_id' => $purchase->farmer_id,
                'payment_date' => now(),
                'amount' => $amount,
                'bonus_percentage' => $bonusPct,
                'bonus_amount' => $bonusAmount,
                'total_payout' => $totalPayout,
                'days_delayed' => $daysDelayed,
                'bonus_calculation_mode' => $bonusMode,
                'payment_method' => $method,
                'transaction_reference' => $reference,
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
            ]);

            // Re-calculate paid and balance due on purchase
            $newPaid = bcadd((string) $purchase->paid_amount, $amount, 2);
            $newBalance = bcsub((string) $purchase->net_payable_amount, $newPaid, 2);

            $purchase->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
            ]);

            $logDesc = "Payment of {$amount} recorded for purchase {$purchase->purchase_number} (Voucher: {$voucherNumber})";
            if (bccomp($bonusAmount, '0.00', 2) > 0) {
                $logDesc .= " with delayed payment bonus of {$bonusAmount} (Total Payout: {$totalPayout})";
            }

            AuditLogger::log('farmer_payment.recorded', $payment, [], $payment->toArray(), $logDesc, $userId);

            return $payment;
        });

        try {
            $settings = app(SettingsService::class);
            if ($settings->bool('whatsapp.enabled') && $settings->bool('whatsapp.auto_notify_payment')) {
                $payment->loadMissing('farmer');
                if (! empty($payment->farmer?->mobile)) {
                    $wa = app(WhatsAppService::class);
                    $wa->send($payment->farmer->mobile, $wa->formatPaymentMessage($payment));
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $payment;
    }

    /**
     * Reverse a finalized purchase: traceable reversal in inventory and status update.
     */
    public function reverse(Purchase $purchase, string $reason, int $userId): void
    {
        if (! $purchase->isFinalized()) {
            throw new RuntimeException("Only finalized purchases can be reversed. Current status: {$purchase->status}");
        }
        if (blank($reason)) {
            throw new InvalidArgumentException('A mandatory reason is required to reverse a finalized transaction.');
        }

        DB::transaction(function () use ($purchase, $reason, $userId) {
            // 1. Post reversing stock outward transaction in ledger
            InventoryTransaction::create([
                'warehouse_id' => $purchase->warehouse_id,
                'crop_id' => $purchase->crop_id,
                'crop_variety_id' => $purchase->crop_variety_id,
                'transaction_date' => now(),
                'quantity_kg' => bcmul($purchase->net_weight_kg, '-1', 3),
                'quantity_qtl' => bcmul($purchase->net_weight_qtl, '-1', 3),
                'source_type' => 'reversal',
                'source_id' => $purchase->id,
                'reference_number' => $purchase->purchase_number,
                'direction' => 'out',
                'description' => "Reversal of purchase {$purchase->purchase_number}: {$reason}",
                'created_by' => $userId,
            ]);

            // 2. Mark purchase reversed
            $purchase->update([
                'status' => Purchase::STATUS_REVERSED,
                'reversed_at' => now(),
                'reversed_by' => $userId,
                'reversal_reason' => $reason,
                'updated_by' => $userId,
            ]);

            AuditLogger::log('purchase.reversed', $purchase, ['status' => 'finalized'], ['status' => 'reversed', 'reason' => $reason], "Reversed purchase {$purchase->purchase_number}", $userId);
        });
    }
}
