<?php

namespace App\Services;

use App\Models\AccountTransfer;
use App\Models\CashAccount;
use App\Models\CustomerReceipt;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\SalesInvoice;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class FinanceService
{
    /**
     * Record an operating expense voucher and deduct from cash/bank account if selected.
     */
    public function recordExpense(array $data, int $userId): Expense
    {
        $amount = bc_round((string) $data['amount'], 2);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new InvalidArgumentException('Expense amount must be greater than zero.');
        }

        return DB::transaction(function () use ($data, $amount, $userId) {
            $voucherNumber = DocumentNumber::next('expense');

            $expense = Expense::create([
                'voucher_number' => $voucherNumber,
                'expense_date' => $data['expense_date'] ?? now(),
                'expense_category_id' => $data['expense_category_id'],
                'cash_account_id' => ! empty($data['cash_account_id']) ? $data['cash_account_id'] : null,
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'paid_to' => $data['paid_to'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status' => Expense::STATUS_APPROVED,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
            ]);

            // Deduct balance from cash account if linked
            if ($expense->cash_account_id) {
                $account = CashAccount::lockForUpdate()->find($expense->cash_account_id);
                if ($account) {
                    $newBalance = bcsub((string) $account->current_balance, $amount, 2);
                    $account->update(['current_balance' => $newBalance]);
                }
            }

            AuditLogger::log('expense.recorded', $expense, [], $expense->toArray(), "Recorded expense voucher {$voucherNumber} for {$amount}", $userId);

            return $expense;
        });
    }

    /**
     * Reverse an expense voucher and restore account balance.
     */
    public function reverseExpense(Expense $expense, string $reason, int $userId): void
    {
        if ($expense->isReversed()) {
            throw new RuntimeException("Expense {$expense->voucher_number} is already reversed.");
        }
        if (blank($reason)) {
            throw new InvalidArgumentException('A mandatory reason is required to reverse an expense.');
        }

        DB::transaction(function () use ($expense, $reason, $userId) {
            if ($expense->cash_account_id) {
                $account = CashAccount::lockForUpdate()->find($expense->cash_account_id);
                if ($account) {
                    $newBalance = bcadd((string) $account->current_balance, (string) $expense->amount, 2);
                    $account->update(['current_balance' => $newBalance]);
                }
            }

            $expense->update([
                'status' => Expense::STATUS_REVERSED,
                'reversed_at' => now(),
                'reversed_by' => $userId,
                'reversal_reason' => $reason,
            ]);

            AuditLogger::log('expense.reversed', $expense, ['status' => 'approved'], ['status' => 'reversed', 'reason' => $reason], "Reversed expense voucher {$expense->voucher_number}", $userId);
        });
    }

    /**
     * Transfer funds between cash and bank accounts (contra entry).
     */
    public function transferAccount(array $data, int $userId): AccountTransfer
    {
        $fromId = (int) $data['from_account_id'];
        $toId = (int) $data['to_account_id'];

        if ($fromId === $toId) {
            throw new InvalidArgumentException('Source and destination accounts must be different.');
        }

        $amount = bc_round((string) $data['amount'], 2);
        if (bccomp($amount, '0.00', 2) <= 0) {
            throw new InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        return DB::transaction(function () use ($data, $fromId, $toId, $amount, $userId) {
            $fromAccount = CashAccount::lockForUpdate()->findOrFail($fromId);
            $toAccount = CashAccount::lockForUpdate()->findOrFail($toId);

            $transferNumber = 'TRF/' . date('Ymd') . '/' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $transfer = AccountTransfer::create([
                'transfer_number' => $transferNumber,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'from_account_id' => $fromId,
                'to_account_id' => $toId,
                'amount' => $amount,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Update balances
            $fromAccount->update(['current_balance' => bcsub((string) $fromAccount->current_balance, $amount, 2)]);
            $toAccount->update(['current_balance' => bcadd((string) $toAccount->current_balance, $amount, 2)]);

            AuditLogger::log('account.transferred', $transfer, [], $transfer->toArray(), "Transferred {$amount} from {$fromAccount->name} to {$toAccount->name}", $userId);

            return $transfer;
        });
    }

    /**
     * Retrieve complete Daybook for a specific date (all inflows & outflows).
     */
    public function getDaybook(string|CarbonInterface $date): array
    {
        $target = Carbon::parse($date)->toDateString();

        // 1. Inflows: Customer Receipts
        $receipts = CustomerReceipt::with('customer', 'invoice')
            ->whereDate('receipt_date', $target)
            ->whereNull('reversed_at')
            ->get()
            ->map(fn ($r) => [
                'type' => 'Customer Receipt',
                'voucher' => $r->receipt_number,
                'party' => $r->customer->name,
                'mode' => $r->method_label,
                'reference' => $r->transaction_reference,
                'inflow' => $r->amount,
                'outflow' => '0.00',
                'description' => "Invoice: {$r->invoice->invoice_number}",
            ]);

        // 2. Outflows: Farmer Payments
        $payments = PurchasePayment::with('farmer', 'purchase')
            ->whereDate('payment_date', $target)
            ->whereNull('reversed_at')
            ->get()
            ->map(fn ($p) => [
                'type' => 'Farmer Payment',
                'voucher' => $p->voucher_number,
                'party' => $p->farmer->name,
                'mode' => $p->method_label,
                'reference' => $p->transaction_reference,
                'inflow' => '0.00',
                'outflow' => $p->effective_payout,
                'description' => "Purchase: {$p->purchase->purchase_number}" . (bccomp((string) $p->bonus_amount, '0.00', 2) > 0 ? " (incl. bonus " . money($p->bonus_amount) . ")" : ''),
            ]);

        // 3. Outflows: Expenses
        $expenses = Expense::with('category')
            ->whereDate('expense_date', $target)
            ->where('status', Expense::STATUS_APPROVED)
            ->get()
            ->map(fn ($e) => [
                'type' => 'Operating Expense',
                'voucher' => $e->voucher_number,
                'party' => $e->paid_to,
                'mode' => $e->method_label,
                'reference' => $e->transaction_reference,
                'inflow' => '0.00',
                'outflow' => $e->amount,
                'description' => "{$e->category->name}: {$e->remarks}",
            ]);

        $entries = $receipts->concat($payments)->concat($expenses);

        $totalInflow = '0.00';
        $totalOutflow = '0.00';
        foreach ($entries as $e) {
            $totalInflow = bcadd($totalInflow, (string) $e['inflow'], 2);
            $totalOutflow = bcadd($totalOutflow, (string) $e['outflow'], 2);
        }

        $netFlow = bcsub($totalInflow, $totalOutflow, 2);

        return [
            'date' => $target,
            'entries' => $entries,
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_flow' => $netFlow,
        ];
    }

    /**
     * Compute authentic Profit & Loss summary between two dates directly from DB transactions.
     */
    public function getProfitAndLoss(string|CarbonInterface $from, string|CarbonInterface $to): array
    {
        $startDate = Carbon::parse($from)->startOfDay();
        $endDate = Carbon::parse($to)->endOfDay();

        // 1. Revenue: Total Crop Sales from finalized invoices
        $totalSales = (string) SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');
        $totalSales = bc_round($totalSales, 2);

        // 2. Cost of Goods Sold (COGS): Total Crop Purchases from finalized purchases
        $totalPurchases = (string) Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('net_payable_amount');
        $totalPurchases = bc_round($totalPurchases, 2);

        // 3. Gross Profit = Sales - Purchases
        $grossProfit = bcsub($totalSales, $totalPurchases, 2);

        // 4. Operating Expenses: categorized
        $expenseCategories = ExpenseCategory::with(['expenses' => function ($q) use ($startDate, $endDate) {
            $q->where('status', Expense::STATUS_APPROVED)
              ->whereBetween('expense_date', [$startDate, $endDate]);
        }])->get();

        $categoryBreakdown = [];
        $totalExpenses = '0.00';

        foreach ($expenseCategories as $cat) {
            $sum = (string) $cat->expenses->sum('amount');
            $sum = bc_round($sum, 2);
            if (bccomp($sum, '0.00', 2) > 0) {
                $categoryBreakdown[$cat->name] = $sum;
                $totalExpenses = bcadd($totalExpenses, $sum, 2);
            }
        }

        // Include delayed payment bonuses paid to farmers as an operating incentive expense
        $bonusSum = (string) PurchasePayment::whereNull('reversed_at')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('bonus_amount');
        $bonusSum = bc_round($bonusSum, 2);
        if (bccomp($bonusSum, '0.00', 2) > 0) {
            $categoryBreakdown['Farmer Delayed Payment Bonus'] = $bonusSum;
            $totalExpenses = bcadd($totalExpenses, $bonusSum, 2);
        }

        // 5. Net Profit / (Loss) = Gross Profit - Operating Expenses
        $netProfit = bcsub($grossProfit, $totalExpenses, 2);

        return [
            'from' => $startDate->toDateString(),
            'to' => $endDate->toDateString(),
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'gross_profit' => $grossProfit,
            'expenses_by_category' => $categoryBreakdown,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
        ];
    }
}
