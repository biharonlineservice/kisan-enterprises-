<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Services\FinancialYear;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DaybookController extends Controller
{
    public function __construct(private FinanceService $finance) {}

    public function daybook(Request $request): View
    {
        $data = $this->prepareDaybookData($request);

        return view('accounts.daybook', $data);
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $this->prepareDaybookData($request);
        $date = $data['daybook']['date'];
        $entries = $data['daybook']['entries'];
        $filename = "daybook-{$date}.csv";

        return response()->stream(function () use ($entries, $date) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Microsoft Excel

            fputcsv($handle, [
                'Date',
                'Voucher Type',
                'Voucher Number',
                'Party / Beneficiary',
                'Payment Mode',
                'Transaction Reference',
                'Inflow (INR)',
                'Outflow (INR)',
                'Net Amount (INR)',
                'Description / Particulars',
            ]);

            foreach ($entries as $e) {
                $net = bccomp((string) $e['inflow'], '0.00', 2) > 0 ? (string) $e['inflow'] : ('-' . (string) $e['outflow']);
                fputcsv($handle, [
                    $date,
                    $e['type'],
                    $e['voucher'],
                    $e['party'],
                    $e['mode'],
                    $e['reference'] ?? '',
                    $e['inflow'],
                    $e['outflow'],
                    $net,
                    $e['description'] ?? '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function profitAndLoss(Request $request): View
    {
        $data = $this->prepareProfitAndLossData($request);

        return view('accounts.profit_and_loss', $data);
    }

    public function exportProfitAndLoss(Request $request): StreamedResponse
    {
        $data = $this->prepareProfitAndLossData($request);
        $report = $data['report'];
        $from = $data['from'];
        $to = $data['to'];
        $filename = "profit-and-loss-{$from}-to-{$to}.csv";

        return response()->stream(function () use ($data, $report, $from, $to) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Microsoft Excel

            fputcsv($handle, [setting('business.name', 'Kisan Enterprises') . ' - Profit & Loss Statement']);
            fputcsv($handle, ['Reporting Period', "{$from} to {$to}"]);
            fputcsv($handle, ['Financial Year', FinancialYear::label()]);
            fputcsv($handle, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Particulars / Account Head', 'Type / Flow', 'Amount (INR)', '% of Revenue']);

            // 1. Revenue
            fputcsv($handle, ['1. REVENUE FROM OPERATIONS', '', '', '']);
            fputcsv($handle, ['Crop Sales (Finalized Sales Invoices)', 'Credit (+)', $report['total_sales'], '100.00%']);
            fputcsv($handle, []);

            // 2. Cost of Goods Sold
            $cogsPct = bccomp($report['total_sales'], '0.00', 2) > 0
                ? bcmul(bcdiv($report['total_purchases'], $report['total_sales'], 4), '100', 2)
                : '0.00';
            fputcsv($handle, ['2. COST OF GOODS SOLD (COGS)', '', '', '']);
            fputcsv($handle, ['Crop Purchases (Farmer Procurement)', 'Debit (-)', $report['total_purchases'], $cogsPct . '%']);
            fputcsv($handle, []);

            // 3. Gross Profit
            fputcsv($handle, [
                'GROSS TRADING PROFIT (1 - 2)',
                bccomp($report['gross_profit'], '0.00', 2) >= 0 ? 'Surplus' : 'Deficit',
                $report['gross_profit'],
                $data['grossMarginPct'] . '%'
            ]);
            fputcsv($handle, []);

            // 4. Operating Expenses
            fputcsv($handle, ['3. OPERATING & ADMINISTRATIVE EXPENSES', '', '', '']);
            foreach ($report['expenses_by_category'] as $catName => $amount) {
                $catPct = bccomp($report['total_sales'], '0.00', 2) > 0
                    ? bcmul(bcdiv($amount, $report['total_sales'], 4), '100', 2)
                    : '0.00';
                fputcsv($handle, [$catName, 'Debit (-)', $amount, $catPct . '%']);
            }
            fputcsv($handle, [
                'Total Operating Expenses',
                'Debit (-)',
                $report['total_expenses'],
                $data['expenseRatioPct'] . '%'
            ]);
            fputcsv($handle, []);

            // 5. Net Profit / (Loss)
            fputcsv($handle, [
                'NET OPERATING PROFIT / (LOSS)',
                bccomp($report['net_profit'], '0.00', 2) >= 0 ? 'Net Profit' : 'Net Loss',
                $report['net_profit'],
                $data['netMarginPct'] . '%'
            ]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Parse and compute Profit & Loss report data, period presets, and financial margins.
     */
    private function prepareProfitAndLossData(Request $request): array
    {
        $period = $request->input('period', 'this_month');

        if ($period === 'last_month') {
            $from = now()->subMonth()->startOfMonth()->toDateString();
            $to = now()->subMonth()->endOfMonth()->toDateString();
        } elseif ($period === 'this_quarter') {
            $from = now()->firstOfQuarter()->toDateString();
            $to = now()->toDateString();
        } elseif ($period === 'this_fy') {
            $from = FinancialYear::start()->toDateString();
            $to = now()->toDateString();
        } elseif ($period === 'custom') {
            $from = $request->input('from', now()->startOfMonth()->toDateString());
            $to = $request->input('to', now()->toDateString());
        } else {
            // Default: this_month
            $period = 'this_month';
            $from = $request->input('from', now()->startOfMonth()->toDateString());
            $to = $request->input('to', now()->toDateString());
        }

        // Validate date strings
        try {
            $from = Carbon::parse($from)->toDateString();
        } catch (\Throwable) {
            $from = now()->startOfMonth()->toDateString();
        }

        try {
            $to = Carbon::parse($to)->toDateString();
        } catch (\Throwable) {
            $to = now()->toDateString();
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $report = $this->finance->getProfitAndLoss($from, $to);

        $totalSales = $report['total_sales'];
        $grossProfit = $report['gross_profit'];
        $totalExpenses = $report['total_expenses'];
        $netProfit = $report['net_profit'];

        $grossMarginPct = bccomp($totalSales, '0.00', 2) > 0
            ? bcmul(bcdiv($grossProfit, $totalSales, 4), '100', 2)
            : '0.00';

        $netMarginPct = bccomp($totalSales, '0.00', 2) > 0
            ? bcmul(bcdiv($netProfit, $totalSales, 4), '100', 2)
            : '0.00';

        $expenseRatioPct = bccomp($totalSales, '0.00', 2) > 0
            ? bcmul(bcdiv($totalExpenses, $totalSales, 4), '100', 2)
            : '0.00';

        return [
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'grossMarginPct' => $grossMarginPct,
            'netMarginPct' => $netMarginPct,
            'expenseRatioPct' => $expenseRatioPct,
        ];
    }

    /**
     * Parse daybook query parameters, calculate breakdown metrics, and apply filters.
     */
    private function prepareDaybookData(Request $request): array
    {
        $rawDate = $request->input('date', now()->toDateString());
        try {
            $date = Carbon::parse($rawDate)->toDateString();
        } catch (\Throwable) {
            $date = now()->toDateString();
        }

        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();

        $rawDaybook = $this->finance->getDaybook($date);
        /** @var Collection $allEntries */
        $allEntries = $rawDaybook['entries'];

        $totalReceipts = '0.00';
        $totalPayments = '0.00';
        $totalExpenses = '0.00';

        $counts = [
            'all' => $allEntries->count(),
            'receipts' => 0,
            'payments' => 0,
            'expenses' => 0,
        ];

        foreach ($allEntries as $e) {
            if ($e['type'] === 'Customer Receipt') {
                $totalReceipts = bcadd($totalReceipts, (string) $e['inflow'], 2);
                $counts['receipts']++;
            } elseif ($e['type'] === 'Farmer Payment') {
                $totalPayments = bcadd($totalPayments, (string) $e['outflow'], 2);
                $counts['payments']++;
            } elseif ($e['type'] === 'Operating Expense') {
                $totalExpenses = bcadd($totalExpenses, (string) $e['outflow'], 2);
                $counts['expenses']++;
            }
        }

        $tab = $request->input('tab', 'all');
        $filtered = match ($tab) {
            'receipts' => $allEntries->where('type', 'Customer Receipt'),
            'payments' => $allEntries->where('type', 'Farmer Payment'),
            'expenses' => $allEntries->where('type', 'Operating Expense'),
            default => $allEntries,
        };

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $term = mb_strtolower($q);
            $filtered = $filtered->filter(function ($e) use ($term) {
                return str_contains(mb_strtolower($e['voucher']), $term)
                    || str_contains(mb_strtolower($e['party']), $term)
                    || str_contains(mb_strtolower($e['mode']), $term)
                    || str_contains(mb_strtolower((string) ($e['reference'] ?? '')), $term)
                    || str_contains(mb_strtolower((string) ($e['description'] ?? '')), $term);
            });
        }

        $daybook = [
            'date' => $date,
            'entries' => $filtered,
            'total_inflow' => $rawDaybook['total_inflow'],
            'total_outflow' => $rawDaybook['total_outflow'],
            'net_flow' => $rawDaybook['net_flow'],
        ];

        return [
            'daybook' => $daybook,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'tab' => $tab,
            'q' => $q,
            'counts' => $counts,
            'totalReceipts' => $totalReceipts,
            'totalPayments' => $totalPayments,
            'totalExpenses' => $totalExpenses,
        ];
    }
}
