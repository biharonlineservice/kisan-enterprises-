<?php

namespace App\Services;

use App\Models\CustomerReceipt;
use App\Models\InventoryTransaction;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class SalesService
{
    /**
     * Compute decimal-safe line totals and aggregate invoice amounts.
     */
    public static function calculate(array $items, string|float $discount = '0.00', string|float $globalTaxRate = '0.00'): array
    {
        $discountAmount = bc_round((string) $discount, 2);
        $taxRate = bc_round((string) $globalTaxRate, 2);

        $subtotal = '0.00';
        $totalTax = '0.00';
        $calculatedItems = [];

        foreach ($items as $item) {
            $qtyQtl = isset($item['quantity_qtl'])
                ? bc_round((string) $item['quantity_qtl'], 3)
                : bcdiv(bc_round((string) ($item['quantity_kg'] ?? '0'), 3), '100', 3);

            $qtyKg = isset($item['quantity_kg'])
                ? bc_round((string) $item['quantity_kg'], 3)
                : bcmul($qtyQtl, '100', 3);

            if (bccomp($qtyQtl, '0', 3) <= 0) {
                throw new InvalidArgumentException('Quantity for all items must be greater than zero.');
            }

            $rate = bc_round((string) $item['rate_per_qtl'], 2);
            $lineBase = bc_round(bcmul($qtyQtl, $rate, 4), 2);

            $lineTaxRate = isset($item['tax_rate']) ? bc_round((string) $item['tax_rate'], 2) : $taxRate;
            $lineTax = '0.00';
            if (bccomp($lineTaxRate, '0', 2) > 0) {
                $lineTax = bc_round(bcmul($lineBase, bcdiv($lineTaxRate, '100', 4), 4), 2);
            }

            $lineTotal = bcadd($lineBase, $lineTax, 2);

            $subtotal = bcadd($subtotal, $lineBase, 2);
            $totalTax = bcadd($totalTax, $lineTax, 2);

            $calculatedItems[] = array_merge($item, [
                'quantity_kg' => $qtyKg,
                'quantity_qtl' => $qtyQtl,
                'rate_per_qtl' => $rate,
                'tax_rate' => $lineTaxRate,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ]);
        }

        $gross = bcadd($subtotal, $totalTax, 2);
        $net = bcsub($gross, $discountAmount, 2);
        if (bccomp($net, '0', 2) < 0) {
            $net = '0.00';
        }

        // Standard rounding off to nearest integer rupee
        $roundedNet = bc_round((string) round((float) $net), 2);
        $roundOff = bcsub($roundedNet, $net, 2);

        return [
            'items' => $calculatedItems,
            'subtotal_amount' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $totalTax,
            'discount_amount' => $discountAmount,
            'round_off' => $roundOff,
            'total_amount' => $roundedNet,
        ];
    }

    /**
     * Create a sales invoice (saved as draft or finalized).
     */
    public function createInvoice(array $data, array $items, int $userId, bool $finalize = false): SalesInvoice
    {
        return DB::transaction(function () use ($data, $items, $userId, $finalize) {
            $calc = self::calculate(
                $items,
                $data['discount_amount'] ?? '0.00',
                $data['tax_rate'] ?? '0.00'
            );

            $invoiceNumber = DocumentNumber::next('sales_invoice');

            $invoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $data['invoice_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'customer_id' => $data['customer_id'],
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'status' => SalesInvoice::STATUS_DRAFT,
                'subtotal_amount' => $calc['subtotal_amount'],
                'tax_rate' => $calc['tax_rate'],
                'tax_amount' => $calc['tax_amount'],
                'discount_amount' => $calc['discount_amount'],
                'round_off' => $calc['round_off'],
                'total_amount' => $calc['total_amount'],
                'paid_amount' => '0.00',
                'balance_due' => $calc['total_amount'],
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'transporter_name' => $data['transporter_name'] ?? null,
                'eway_bill_number' => $data['eway_bill_number'] ?? null,
                'delivery_destination' => $data['delivery_destination'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($calc['items'] as $item) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'crop_id' => $item['crop_id'],
                    'crop_variety_id' => $item['crop_variety_id'] ?? null,
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity_kg' => $item['quantity_kg'],
                    'quantity_qtl' => $item['quantity_qtl'],
                    'rate_per_qtl' => $item['rate_per_qtl'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $item['tax_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }

            AuditLogger::log('sales_invoice.created', $invoice, [], $invoice->toArray(), "Created sales invoice draft {$invoice->invoice_number}", $userId);

            if ($finalize) {
                $this->finalizeInvoice(
                    $invoice,
                    $userId,
                    $data['initial_paid_amount'] ?? '0.00',
                    $data['payment_method'] ?? 'bank_transfer',
                    $data['payment_reference'] ?? null
                );
            }

            return $invoice;
        });
    }

    /**
     * Finalize sales invoice: verifies warehouse stock availability, deducts inventory, records initial payment if any.
     */
    public function finalizeInvoice(
        SalesInvoice $invoice,
        int $userId,
        string|float $initialPayment = '0.00',
        string $paymentMethod = 'bank_transfer',
        ?string $paymentRef = null
    ): void {
        if (! $invoice->isDraft()) {
            throw new RuntimeException("Invoice {$invoice->invoice_number} is already {$invoice->status} and cannot be finalized.");
        }

        $invoice->load('items.crop', 'items.warehouse', 'customer');

        // Stock availability verification
        $allowNegative = (bool) setting('inventory.allow_negative_stock', false);
        if (! $allowNegative) {
            foreach ($invoice->items as $item) {
                $available = InventoryTransaction::getStock($item->warehouse_id, $item->crop_id, $item->crop_variety_id);
                if (bccomp($available, (string) $item->quantity_qtl, 3) < 0) {
                    throw new RuntimeException("Insufficient stock in {$item->warehouse->name} for {$item->crop->name}. Available: {$available} Qtl, Requested: {$item->quantity_qtl} Qtl.");
                }
            }
        }

        DB::transaction(function () use ($invoice, $userId, $initialPayment, $paymentMethod, $paymentRef) {
            // Post outward movement for each item to InventoryTransaction ledger
            foreach ($invoice->items as $item) {
                InventoryTransaction::create([
                    'warehouse_id' => $item->warehouse_id,
                    'crop_id' => $item->crop_id,
                    'crop_variety_id' => $item->crop_variety_id,
                    'transaction_date' => $invoice->invoice_date,
                    'quantity_kg' => bcmul((string) $item->quantity_kg, '-1', 3),
                    'quantity_qtl' => bcmul((string) $item->quantity_qtl, '-1', 3),
                    'source_type' => 'sale',
                    'source_id' => $invoice->id,
                    'reference_number' => $invoice->invoice_number,
                    'direction' => 'out',
                    'description' => "Dispatch to {$invoice->customer->name} ({$invoice->invoice_number})",
                    'created_by' => $userId,
                ]);
            }

            // Record initial customer receipt if provided
            $initialPaid = bc_round((string) $initialPayment, 2);
            if (bccomp($initialPaid, '0.00', 2) > 0) {
                $this->recordReceipt($invoice, $initialPaid, $paymentMethod, $paymentRef, null, 'Initial receipt at dispatch', $userId);
            }

            $invoice->update([
                'status' => SalesInvoice::STATUS_FINALIZED,
                'updated_by' => $userId,
            ]);

            // Update linked sales order to dispatched if currently confirmed
            if ($invoice->sales_order_id) {
                $order = SalesOrder::find($invoice->sales_order_id);
                if ($order && $order->status === SalesOrder::STATUS_CONFIRMED) {
                    $order->update([
                        'status' => SalesOrder::STATUS_DISPATCHED,
                        'updated_by' => $userId,
                    ]);
                    AuditLogger::log('sales_order.dispatched', $order, ['status' => 'confirmed'], ['status' => 'dispatched'], "Dispatched via invoice {$invoice->invoice_number}", $userId);
                }
            }

            AuditLogger::log('sales_invoice.finalized', $invoice, ['status' => 'draft'], ['status' => 'finalized'], "Finalized sales invoice {$invoice->invoice_number}", $userId);
        });
    }

    /**
     * Record receipt against a customer sales invoice.
     */
    public function recordReceipt(
        SalesInvoice $invoice,
        string|float $amount,
        string $method = 'bank_transfer',
        ?string $reference = null,
        ?string $bankName = null,
        ?string $notes = null,
        ?int $userId = null
    ): CustomerReceipt {
        $amount = bc_round((string) $amount, 2);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new InvalidArgumentException('Receipt amount must be greater than zero.');
        }

        return DB::transaction(function () use ($invoice, $amount, $method, $reference, $bankName, $notes, $userId) {
            $receiptNumber = DocumentNumber::next('customer_receipt');

            $receipt = CustomerReceipt::create([
                'receipt_number' => $receiptNumber,
                'receipt_date' => now(),
                'customer_id' => $invoice->customer_id,
                'sales_invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_reference' => $reference,
                'bank_name' => $bankName,
                'notes' => $notes,
                'created_by' => $userId ?? auth()->id(),
            ]);

            $newPaid = bcadd((string) $invoice->paid_amount, $amount, 2);
            $newBalance = bcsub((string) $invoice->total_amount, $newPaid, 2);

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
            ]);

            AuditLogger::log('customer_receipt.recorded', $receipt, [], $receipt->toArray(), "Receipt of {$amount} recorded for invoice {$invoice->invoice_number} (Voucher: {$receiptNumber})", $userId);

            return $receipt;
        });
    }

    /**
     * Reverse a finalized sales invoice: restores stock into inventory ledger and marks status reversed.
     */
    public function reverseInvoice(SalesInvoice $invoice, string $reason, int $userId): void
    {
        if (! $invoice->isFinalized()) {
            throw new RuntimeException("Only finalized sales invoices can be reversed. Current status: {$invoice->status}");
        }
        if (blank($reason)) {
            throw new InvalidArgumentException('A mandatory reason is required to reverse a finalized transaction.');
        }

        $invoice->load('items');

        DB::transaction(function () use ($invoice, $reason, $userId) {
            // Restore inventory for each item with reversing inward movement
            foreach ($invoice->items as $item) {
                InventoryTransaction::create([
                    'warehouse_id' => $item->warehouse_id,
                    'crop_id' => $item->crop_id,
                    'crop_variety_id' => $item->crop_variety_id,
                    'transaction_date' => now(),
                    'quantity_kg' => $item->quantity_kg, // positive restoration
                    'quantity_qtl' => $item->quantity_qtl,
                    'source_type' => 'reversal',
                    'source_id' => $invoice->id,
                    'reference_number' => $invoice->invoice_number,
                    'direction' => 'in',
                    'description' => "Reversal of sales invoice {$invoice->invoice_number}: {$reason}",
                    'created_by' => $userId,
                ]);
            }

            $invoice->update([
                'status' => SalesInvoice::STATUS_REVERSED,
                'reversed_at' => now(),
                'reversed_by' => $userId,
                'reversal_reason' => $reason,
                'updated_by' => $userId,
            ]);

            AuditLogger::log('sales_invoice.reversed', $invoice, ['status' => 'finalized'], ['status' => 'reversed', 'reason' => $reason], "Reversed sales invoice {$invoice->invoice_number}", $userId);
        });
    }

    /**
     * Mark sales invoice as delivered to the buyer / mill.
     */
    public function markDelivered(SalesInvoice $invoice, array $data, int $userId): SalesInvoice
    {
        if (! $invoice->isFinalized()) {
            throw new RuntimeException("Only finalized invoices can be marked as delivered. Current status: {$invoice->status}");
        }

        return DB::transaction(function () use ($invoice, $data, $userId) {
            $deliveredAt = ! empty($data['delivered_at']) ? Carbon::parse($data['delivered_at']) : now();
            $receivedBy = $data['received_by'] ?? null;
            $deliveryNotes = $data['delivery_notes'] ?? null;

            $oldState = [
                'delivery_status' => $invoice->delivery_status,
                'delivered_at' => $invoice->delivered_at,
            ];

            $invoice->update([
                'delivery_status' => SalesInvoice::DELIVERY_DELIVERED,
                'delivered_at' => $deliveredAt,
                'delivered_by' => $userId,
                'received_by' => $receivedBy,
                'delivery_notes' => $deliveryNotes,
                'updated_by' => $userId,
            ]);

            // If attached to a SalesOrder, check if all invoices for that order are delivered
            if ($invoice->sales_order_id && $invoice->salesOrder) {
                $order = $invoice->salesOrder;
                $allDelivered = $order->invoices()
                    ->where('status', SalesInvoice::STATUS_FINALIZED)
                    ->where('delivery_status', '!=', SalesInvoice::DELIVERY_DELIVERED)
                    ->count() === 0;

                if ($allDelivered && $order->isDispatched()) {
                    $order->update([
                        'status' => SalesOrder::STATUS_COMPLETED,
                        'updated_by' => $userId,
                    ]);
                    AuditLogger::log('sales_order.completed', $order, ['status' => 'dispatched'], ['status' => 'completed'], "Sales Order {$order->order_number} marked completed upon delivery of invoice {$invoice->invoice_number}", $userId);
                }
            }

            AuditLogger::log('sales_invoice.delivered', $invoice, $oldState, [
                'delivery_status' => 'delivered',
                'delivered_at' => $deliveredAt,
                'received_by' => $receivedBy,
            ], "Marked sales invoice {$invoice->invoice_number} as delivered to buyer", $userId);

            return $invoice;
        });
    }

    /**
     * Revert buyer delivered status back to in-transit.
     */
    public function revertDelivery(SalesInvoice $invoice, int $userId): SalesInvoice
    {
        if (! $invoice->isDelivered()) {
            throw new RuntimeException("Invoice {$invoice->invoice_number} is not currently marked as delivered.");
        }

        return DB::transaction(function () use ($invoice, $userId) {
            $oldState = [
                'delivery_status' => $invoice->delivery_status,
                'delivered_at' => $invoice->delivered_at,
            ];

            $invoice->update([
                'delivery_status' => SalesInvoice::DELIVERY_IN_TRANSIT,
                'delivered_at' => null,
                'delivered_by' => null,
                'received_by' => null,
                'delivery_notes' => null,
                'updated_by' => $userId,
            ]);

            // If sales order was marked completed, bring back to dispatched
            if ($invoice->sales_order_id && $invoice->salesOrder && $invoice->salesOrder->isCompleted()) {
                $invoice->salesOrder->update([
                    'status' => SalesOrder::STATUS_DISPATCHED,
                    'updated_by' => $userId,
                ]);
            }

            AuditLogger::log('sales_invoice.delivery_reverted', $invoice, $oldState, ['delivery_status' => 'in_transit'], "Reverted delivery status of sales invoice {$invoice->invoice_number} to in-transit", $userId);

            return $invoice;
        });
    }
}
