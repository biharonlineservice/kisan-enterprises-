<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\Crop;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\Expense;
use App\Models\Farmer;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\SalesInvoice;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Parse date presets (today, yesterday, this_month, last_month, this_fy, custom).
     */
    public static function resolveDateRange(array $filters): array
    {
        $preset = $filters['preset'] ?? 'this_month';
        $today = now()->startOfDay();

        switch ($preset) {
            case 'today':
                return [$today->toDateString(), $today->toDateString()];
            case 'yesterday':
                $y = now()->subDay()->startOfDay();
                return [$y->toDateString(), $y->toDateString()];
            case 'this_week':
                return [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()];
            case 'last_month':
                $lm = now()->subMonth();
                return [$lm->copy()->startOfMonth()->toDateString(), $lm->copy()->endOfMonth()->toDateString()];
            case 'this_quarter':
                return [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString()];
            case 'this_fy':
                $year = (int) now()->format('Y');
                $startYear = now()->month >= 4 ? $year : $year - 1;
                return ["{$startYear}-04-01", ($startYear + 1).'-03-31'];
            case 'all_time':
                return ['2000-01-01', now()->addYear()->toDateString()];
            case 'custom':
                return [
                    $filters['date_from'] ?? now()->startOfMonth()->toDateString(),
                    $filters['date_to'] ?? now()->toDateString(),
                ];
            case 'this_month':
            default:
                return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        }
    }

    /**
     * 1. Purchases Report: filtered by date, crop, warehouse, farmer, status.
     */
    public function purchaseReport(array $filters): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        return Purchase::with(['farmer', 'crop', 'variety', 'warehouse'])
            ->whereBetween('purchase_date', [$from, $to])
            ->when(! empty($filters['crop_id']), fn ($q) => $q->where('crop_id', $filters['crop_id']))
            ->when(! empty($filters['warehouse_id']), fn ($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->when(! empty($filters['farmer_id']), fn ($q) => $q->where('farmer_id', $filters['farmer_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 2. Monthly Purchase Summary for a financial year.
     */
    public function monthlyPurchaseSummary(int $startYear): array
    {
        $months = [];
        // Indian FY: April to March
        for ($m = 4; $m <= 12; $m++) {
            $months[] = Carbon::createFromDate($startYear, $m, 1);
        }
        for ($m = 1; $m <= 3; $m++) {
            $months[] = Carbon::createFromDate($startYear + 1, $m, 1);
        }

        $summary = [];
        foreach ($months as $carbonMonth) {
            $mStart = $carbonMonth->copy()->startOfMonth()->toDateString();
            $mEnd = $carbonMonth->copy()->endOfMonth()->toDateString();

            $purchases = Purchase::where('status', Purchase::STATUS_FINALIZED)
                ->whereBetween('purchase_date', [$mStart, $mEnd])
                ->get();

            $qtl = '0';
            $gross = '0';
            $net = '0';
            $paid = '0';
            $balance = '0';

            foreach ($purchases as $p) {
                $qtl = bcadd($qtl, (string) $p->net_weight_qtl, 4);
                $gross = bcadd($gross, (string) $p->gross_amount, 2);
                $net = bcadd($net, (string) $p->net_payable, 2);
                $paid = bcadd($paid, (string) $p->paid_amount, 2);
                $balance = bcadd($balance, (string) $p->balance_due, 2);
            }

            $summary[] = [
                'month_name' => $carbonMonth->format('M Y'),
                'count' => $purchases->count(),
                'quantity_qtl' => $qtl,
                'quantity_mt' => bcdiv($qtl, '10', 3),
                'gross_amount' => $gross,
                'net_payable' => $net,
                'paid_amount' => $paid,
                'balance_due' => $balance,
            ];
        }

        return $summary;
    }

    /**
     * 3. Sales Report: filtered by date, customer, status.
     */
    public function salesReport(array $filters): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        return SalesInvoice::with(['customer', 'items.crop', 'items.warehouse'])
            ->whereBetween('invoice_date', [$from, $to])
            ->when(! empty($filters['customer_id']), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderBy('invoice_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 4. Monthly Sales Summary for a financial year.
     */
    public function monthlySalesSummary(int $startYear): array
    {
        $months = [];
        for ($m = 4; $m <= 12; $m++) {
            $months[] = Carbon::createFromDate($startYear, $m, 1);
        }
        for ($m = 1; $m <= 3; $m++) {
            $months[] = Carbon::createFromDate($startYear + 1, $m, 1);
        }

        $summary = [];
        foreach ($months as $carbonMonth) {
            $mStart = $carbonMonth->copy()->startOfMonth()->toDateString();
            $mEnd = $carbonMonth->copy()->endOfMonth()->toDateString();

            $invoices = SalesInvoice::with('items')
                ->where('status', SalesInvoice::STATUS_FINALIZED)
                ->whereBetween('invoice_date', [$mStart, $mEnd])
                ->get();

            $qtl = '0';
            $subtotal = '0';
            $tax = '0';
            $net = '0';
            $received = '0';
            $balance = '0';

            foreach ($invoices as $inv) {
                foreach ($inv->items as $item) {
                    $qtl = bcadd($qtl, (string) $item->weight_qtl, 4);
                }
                $subtotal = bcadd($subtotal, (string) $inv->subtotal, 2);
                $tax = bcadd($tax, (string) $inv->tax_amount, 2);
                $net = bcadd($net, (string) $inv->net_amount, 2);
                $received = bcadd($received, (string) $inv->received_amount, 2);
                $balance = bcadd($balance, (string) $inv->balance_due, 2);
            }

            $summary[] = [
                'month_name' => $carbonMonth->format('M Y'),
                'count' => $invoices->count(),
                'quantity_qtl' => $qtl,
                'quantity_mt' => bcdiv($qtl, '10', 3),
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'net_amount' => $net,
                'received_amount' => $received,
                'balance_due' => $balance,
            ];
        }

        return $summary;
    }

    /**
     * 5. Current Stock Balance by Warehouse and Crop.
     */
    public function stockBalanceReport(array $filters = []): Collection
    {
        $query = InventoryTransaction::select(
            'warehouse_id',
            'crop_id',
            'crop_variety_id',
            DB::raw('SUM(quantity_qtl) as total_weight_qtl')
        )
            ->groupBy('warehouse_id', 'crop_id', 'crop_variety_id')
            ->havingRaw('total_weight_qtl > 0');

        if (! empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (! empty($filters['crop_id'])) {
            $query->where('crop_id', $filters['crop_id']);
        }

        $rows = $query->get();

        // Eager load relations
        $warehouseIds = $rows->pluck('warehouse_id')->unique();
        $cropIds = $rows->pluck('crop_id')->unique();
        $varietyIds = $rows->pluck('crop_variety_id')->filter()->unique();

        $warehouses = Warehouse::whereIn('id', $warehouseIds)->get()->keyBy('id');
        $crops = Crop::whereIn('id', $cropIds)->get()->keyBy('id');
        $varieties = \App\Models\CropVariety::whereIn('id', $varietyIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($warehouses, $crops, $varieties) {
            $qtl = (string) $row->total_weight_qtl;
            return [
                'warehouse' => $warehouses[$row->warehouse_id] ?? null,
                'crop' => $crops[$row->crop_id] ?? null,
                'variety' => $varieties[$row->crop_variety_id] ?? null,
                'bags' => (int) round((float) $qtl * 2),
                'weight_qtl' => $qtl,
                'weight_mt' => bcdiv($qtl, '10', 3),
            ];
        });
    }

    /**
     * 6. Stock Movement Register (all inventory ledger lines).
     */
    public function stockMovementRegister(array $filters): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        return InventoryTransaction::with(['warehouse', 'crop', 'cropVariety', 'creator'])
            ->whereBetween('transaction_date', [$from, $to])
            ->when(! empty($filters['warehouse_id']), fn ($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->when(! empty($filters['crop_id']), fn ($q) => $q->where('crop_id', $filters['crop_id']))
            ->when(! empty($filters['direction']), fn ($q) => $q->where('direction', $filters['direction']))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 7. Farmer Outstanding Balances with Aging.
     */
    public function farmerOutstandingReport(): Collection
    {
        $purchases = Purchase::with('farmer')
            ->where('status', Purchase::STATUS_FINALIZED)
            ->where('balance_due', '>', 0)
            ->get();

        $grouped = $purchases->groupBy('farmer_id');
        $result = collect();

        foreach ($grouped as $farmerId => $farmerPurchases) {
            $farmer = $farmerPurchases->first()->farmer;
            if (! $farmer) {
                continue;
            }

            $totalDue = '0';
            $aging = [
                '0_15' => '0',
                '16_30' => '0',
                '31_60' => '0',
                '60_plus' => '0',
            ];

            foreach ($farmerPurchases as $p) {
                $due = (string) $p->balance_due;
                $totalDue = bcadd($totalDue, $due, 2);
                $days = Carbon::parse($p->purchase_date)->diffInDays(now());

                if ($days <= 15) {
                    $aging['0_15'] = bcadd($aging['0_15'], $due, 2);
                } elseif ($days <= 30) {
                    $aging['16_30'] = bcadd($aging['16_30'], $due, 2);
                } elseif ($days <= 60) {
                    $aging['31_60'] = bcadd($aging['31_60'], $due, 2);
                } else {
                    $aging['60_plus'] = bcadd($aging['60_plus'], $due, 2);
                }
            }

            $result->push([
                'farmer' => $farmer,
                'purchase_count' => $farmerPurchases->count(),
                'total_outstanding' => $totalDue,
                'aging' => $aging,
            ]);
        }

        return $result->sortByDesc('total_outstanding')->values();
    }

    /**
     * 8. Customer Outstanding Balances with Aging & Credit Utilization.
     */
    public function customerOutstandingReport(): Collection
    {
        $invoices = SalesInvoice::with('customer')
            ->where('status', SalesInvoice::STATUS_FINALIZED)
            ->where('balance_due', '>', 0)
            ->get();

        $grouped = $invoices->groupBy('customer_id');
        $result = collect();

        foreach ($grouped as $customerId => $custInvoices) {
            $customer = $custInvoices->first()->customer;
            if (! $customer) {
                continue;
            }

            $totalDue = '0';
            $aging = [
                '0_15' => '0',
                '16_30' => '0',
                '31_60' => '0',
                '60_plus' => '0',
            ];

            foreach ($custInvoices as $inv) {
                $due = (string) $inv->balance_due;
                $totalDue = bcadd($totalDue, $due, 2);
                $days = Carbon::parse($inv->invoice_date)->diffInDays(now());

                if ($days <= 15) {
                    $aging['0_15'] = bcadd($aging['0_15'], $due, 2);
                } elseif ($days <= 30) {
                    $aging['16_30'] = bcadd($aging['16_30'], $due, 2);
                } elseif ($days <= 60) {
                    $aging['31_60'] = bcadd($aging['31_60'], $due, 2);
                } else {
                    $aging['60_plus'] = bcadd($aging['60_plus'], $due, 2);
                }
            }

            $creditLimit = (string) ($customer->credit_limit ?? '0');
            $utilization = bccomp($creditLimit, '0', 2) > 0
                ? bc_round(bcdiv(bcmul($totalDue, '100', 4), $creditLimit, 4), 1)
                : null;

            $result->push([
                'customer' => $customer,
                'invoice_count' => $custInvoices->count(),
                'total_outstanding' => $totalDue,
                'credit_limit' => $creditLimit,
                'credit_utilization' => $utilization,
                'aging' => $aging,
            ]);
        }

        return $result->sortByDesc('total_outstanding')->values();
    }

    /**
     * 9. Farmer Payment History.
     */
    public function farmerPaymentHistory(?int $farmerId, array $filters = []): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        return PurchasePayment::with(['farmer', 'purchase', 'cashAccount', 'creator'])
            ->whereBetween('payment_date', [$from, $to])
            ->when($farmerId, fn ($q) => $q->where('farmer_id', $farmerId))
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 10. Customer Receipt History.
     */
    public function customerReceiptHistory(?int $customerId, array $filters = []): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        return CustomerReceipt::with(['customer', 'salesInvoice', 'cashAccount', 'creator'])
            ->whereBetween('receipt_date', [$from, $to])
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->orderBy('receipt_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 11. Expense Summary by Category.
     */
    public function expenseSummary(array $filters = []): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        $expenses = Expense::with(['category', 'account'])
            ->whereBetween('expense_date', [$from, $to])
            ->where('status', Expense::STATUS_APPROVED)
            ->get();

        $grouped = $expenses->groupBy('expense_category_id');
        $result = collect();

        foreach ($grouped as $catId => $catExpenses) {
            $cat = $catExpenses->first()->category;
            $sum = '0';
            foreach ($catExpenses as $e) {
                $sum = bcadd($sum, (string) $e->amount, 2);
            }

            $result->push([
                'category' => $cat,
                'count' => $catExpenses->count(),
                'total_amount' => $sum,
            ]);
        }

        return $result->sortByDesc('total_amount')->values();
    }

    /**
     * 12. Cashbook & Bankbook statements.
     */
    public function accountStatement(int $accountId, array $filters = []): array
    {
        [$from, $to] = self::resolveDateRange($filters);
        $account = CashAccount::findOrFail($accountId);
        $isCash = $account->account_type === 'cash';

        // 1. Fetch inward vouchers (Customer Receipts & Transfers In)
        $receiptsQuery = CustomerReceipt::with('customer')
            ->whereBetween('receipt_date', [$from, $to])
            ->whereNull('reversed_at');

        if ($isCash) {
            $receiptsQuery->where('payment_method', 'cash');
        } else {
            $receiptsQuery->where('payment_method', '!=', 'cash');
            if (! empty($account->bank_name)) {
                $receiptsQuery->where(function ($q) use ($account) {
                    $q->whereNull('bank_name')->orWhere('bank_name', 'like', "%{$account->bank_name}%");
                });
            }
        }

        $receipts = $receiptsQuery->get()->map(fn ($r) => [
            'date' => $r->receipt_date->toDateString(),
            'voucher_no' => $r->receipt_number,
            'particulars' => "Receipt from " . ($r->customer?->name ?? 'Customer'),
            'inflow' => (string) $r->amount,
            'outflow' => '0',
        ]);

        // 2. Fetch outward vouchers (Farmer Payments)
        $paymentsQuery = PurchasePayment::with('farmer')
            ->whereBetween('payment_date', [$from, $to])
            ->whereNull('reversed_at');

        if ($isCash) {
            $paymentsQuery->where('payment_method', 'cash');
        } else {
            $paymentsQuery->where('payment_method', '!=', 'cash');
        }

        $payments = $paymentsQuery->get()->map(fn ($p) => [
            'date' => $p->payment_date?->toDateString() ?? '',
            'voucher_no' => $p->voucher_number,
            'particulars' => "Payment to " . ($p->farmer?->name ?? 'Farmer'),
            'inflow' => '0',
            'outflow' => (string) $p->amount,
        ]);

        // 3. Expenses linked to this cash/bank account
        $expenses = Expense::with('category')
            ->where('cash_account_id', $accountId)
            ->whereBetween('expense_date', [$from, $to])
            ->where('status', Expense::STATUS_APPROVED)
            ->get()
            ->map(fn ($e) => [
                'date' => $e->expense_date->toDateString(),
                'voucher_no' => $e->voucher_number,
                'particulars' => "Expense: " . ($e->category?->name ?? 'General') . " (" . ($e->paid_to ?? $e->remarks ?? '') . ")",
                'inflow' => '0',
                'outflow' => (string) $e->amount,
            ]);

        // 4. Contra / Account transfers
        $transfersIn = AccountTransfer::with('fromAccount')
            ->where('to_account_id', $accountId)
            ->whereBetween('transfer_date', [$from, $to])
            ->get()
            ->map(fn ($t) => [
                'date' => $t->transfer_date->toDateString(),
                'voucher_no' => $t->transfer_number,
                'particulars' => "Transfer In from " . ($t->fromAccount?->name ?? 'Account'),
                'inflow' => (string) $t->amount,
                'outflow' => '0',
            ]);

        $transfersOut = AccountTransfer::with('toAccount')
            ->where('from_account_id', $accountId)
            ->whereBetween('transfer_date', [$from, $to])
            ->get()
            ->map(fn ($t) => [
                'date' => $t->transfer_date->toDateString(),
                'voucher_no' => $t->transfer_number,
                'particulars' => "Transfer Out to " . ($t->toAccount?->name ?? 'Account'),
                'inflow' => '0',
                'outflow' => (string) $t->amount,
            ]);

        $lines = $receipts->concat($payments)->concat($expenses)->concat($transfersIn)->concat($transfersOut)
            ->sortBy('date')
            ->values();

        $totalIn = '0';
        $totalOut = '0';
        foreach ($lines as $line) {
            $totalIn = bcadd($totalIn, $line['inflow'], 2);
            $totalOut = bcadd($totalOut, $line['outflow'], 2);
        }

        return [
            'account' => $account,
            'lines' => $lines,
            'total_inflow' => $totalIn,
            'total_outflow' => $totalOut,
            'net_movement' => bcsub($totalIn, $totalOut, 2),
            'current_balance' => (string) $account->current_balance,
        ];
    }

    /**
     * 15 & 16. Crop-wise Profitability.
     */
    public function cropProfitability(array $filters = []): Collection
    {
        [$from, $to] = self::resolveDateRange($filters);

        $crops = Crop::with('varieties')->where('is_active', true)->get();
        $result = collect();

        foreach ($crops as $crop) {
            // Purchases of this crop
            $purchases = Purchase::where('crop_id', $crop->id)
                ->where('status', Purchase::STATUS_FINALIZED)
                ->whereBetween('purchase_date', [$from, $to])
                ->get();

            $purchasedQtl = '0';
            $purchaseCost = '0';
            foreach ($purchases as $p) {
                $purchasedQtl = bcadd($purchasedQtl, (string) $p->net_weight_qtl, 4);
                $purchaseCost = bcadd($purchaseCost, (string) $p->net_payable, 2);
            }

            // Sales of this crop (via invoice items)
            $salesItems = \App\Models\SalesInvoiceItem::where('crop_id', $crop->id)
                ->whereHas('invoice', fn ($q) => $q->where('status', SalesInvoice::STATUS_FINALIZED)->whereBetween('invoice_date', [$from, $to]))
                ->get();

            $soldQtl = '0';
            $salesRevenue = '0';
            foreach ($salesItems as $item) {
                $soldQtl = bcadd($soldQtl, (string) $item->weight_qtl, 4);
                $salesRevenue = bcadd($salesRevenue, (string) $item->total_amount, 2);
            }

            $grossMargin = bcsub($salesRevenue, $purchaseCost, 2);

            $result->push([
                'crop' => $crop,
                'purchased_qtl' => $purchasedQtl,
                'purchase_cost' => $purchaseCost,
                'sold_qtl' => $soldQtl,
                'sales_revenue' => $salesRevenue,
                'gross_margin' => $grossMargin,
            ]);
        }

        return $result;
    }

    /**
     * 17. Warehouse Capacity Utilization.
     */
    public function warehouseCapacityReport(): Collection
    {
        $warehouses = Warehouse::where('is_active', true)->get();

        return $warehouses->map(function ($wh) {
            $stock = InventoryTransaction::where('warehouse_id', $wh->id)->sum('quantity_qtl') ?? '0';

            $stockQtl = bccomp((string) $stock, '0', 4) > 0 ? (string) $stock : '0';
            $capacity = (string) ($wh->capacity_qtl ?? '0');

            $pct = bccomp($capacity, '0', 2) > 0
                ? bc_round(bcdiv(bcmul($stockQtl, '100', 4), $capacity, 4), 1)
                : '0.0';

            return [
                'warehouse' => $wh,
                'capacity_qtl' => $capacity,
                'current_stock_qtl' => $stockQtl,
                'current_stock_mt' => bcdiv($stockQtl, '10', 3),
                'available_qtl' => bcsub($capacity, $stockQtl, 2),
                'utilization_percent' => $pct,
            ];
        });
    }

    /**
     * 18. Low Stock Alerts (commodities with total stock below threshold, e.g. 50 Qtl).
     */
    public function lowStockAlertReport(string $thresholdQtl = '50.0'): Collection
    {
        $crops = Crop::where('is_active', true)->get();
        $alerts = collect();

        foreach ($crops as $crop) {
            $stock = InventoryTransaction::where('crop_id', $crop->id)->sum('quantity_qtl') ?? '0';

            $stockQtl = (string) $stock;

            if (bccomp($stockQtl, $thresholdQtl, 2) < 0) {
                $alerts->push([
                    'crop' => $crop,
                    'current_stock_qtl' => $stockQtl,
                    'threshold_qtl' => $thresholdQtl,
                ]);
            }
        }

        return $alerts;
    }

    /**
     * 19. Farmer Registration Growth (new farmers enrolled per month).
     */
    public function farmerRegistrationGrowth(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i);
        }

        $result = [];
        foreach ($months as $carbonMonth) {
            $start = $carbonMonth->copy()->startOfMonth();
            $end = $carbonMonth->copy()->endOfMonth();

            $count = Farmer::whereBetween('created_at', [$start, $end])->count();

            $result[] = [
                'month' => $carbonMonth->format('M Y'),
                'count' => $count,
            ];
        }

        return $result;
    }

    /**
     * 20. Cancelled Transactions & Audit Log Report.
     */
    public function cancelledTransactionsReport(array $filters = []): array
    {
        [$from, $to] = self::resolveDateRange($filters);

        $cancelledPurchases = Purchase::with(['farmer', 'crop'])
            ->where('status', Purchase::STATUS_CANCELLED)
            ->whereBetween('purchase_date', [$from, $to])
            ->get();

        $reversedExpenses = Expense::with(['category', 'cashAccount'])
            ->where('status', Expense::STATUS_REVERSED)
            ->whereBetween('expense_date', [$from, $to])
            ->get();

        $auditReversals = AuditLog::with('user')
            ->where('action', 'like', '%.reverse%')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        return [
            'cancelled_purchases' => $cancelledPurchases,
            'reversed_expenses' => $reversedExpenses,
            'reversal_logs' => $auditReversals,
        ];
    }
}
