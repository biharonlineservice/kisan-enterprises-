<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Crop;
use App\Models\Customer;
use App\Models\Farmer;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\SalesInvoice;
use App\Models\Warehouse;
use App\Services\FinancialYear;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportingService $reporting
    ) {}

    /**
     * Central Reports Hub: displays categorized directory of available reports.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $fyStart = FinancialYear::start();

        $totalPurchasesYtd = (string) Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->where('purchase_date', '>=', $fyStart)
            ->sum('net_payable_amount');

        $totalSalesYtd = (string) SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)
            ->where('invoice_date', '>=', $fyStart)
            ->sum('total_amount');

        $totalStockQtl = (string) InventoryTransaction::sum('quantity_qtl');
        if (bccomp($totalStockQtl, '0.00', 2) < 0) {
            $totalStockQtl = '0.00';
        }

        $activeFarmersCount = Farmer::where('is_active', true)->count();
        $activeCustomersCount = Customer::where('is_active', true)->count();
        $activeWarehousesCount = Warehouse::where('is_active', true)->count();

        $purchasesCount = Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->where('purchase_date', '>=', $fyStart)
            ->count();

        $salesCount = SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)
            ->where('invoice_date', '>=', $fyStart)
            ->count();

        return view('reports.index', [
            'user' => $user,
            'warehouses' => Warehouse::where('is_active', true)->get(),
            'crops' => Crop::where('is_active', true)->get(),
            'metrics' => [
                'totalPurchasesYtd' => bc_round($totalPurchasesYtd, 2),
                'totalSalesYtd' => bc_round($totalSalesYtd, 2),
                'totalStockQtl' => bc_round($totalStockQtl, 2),
                'activeFarmersCount' => $activeFarmersCount,
                'activeCustomersCount' => $activeCustomersCount,
                'activeWarehousesCount' => $activeWarehousesCount,
                'purchasesCount' => $purchasesCount,
                'salesCount' => $salesCount,
            ],
        ]);
    }

    /**
     * Purchase Reports: Daily / Range purchases + Monthly FY summary.
     */
    public function purchases(Request $request)
    {
        $this->authorizePermission('reports.purchase');

        $filters = $request->only(['preset', 'date_from', 'date_to', 'crop_id', 'warehouse_id', 'farmer_id', 'status']);
        $purchases = $this->reporting->purchaseReport($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportPurchasesCsv($purchases);
        }

        $fyYear = (int) ($request->get('fy_year') ?? (now()->month >= 4 ? now()->year : now()->year - 1));
        $monthlySummary = $this->reporting->monthlyPurchaseSummary($fyYear);

        return view('reports.purchases', [
            'purchases' => $purchases,
            'monthlySummary' => $monthlySummary,
            'fyYear' => $fyYear,
            'filters' => $filters,
            'crops' => Crop::where('is_active', true)->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
            'farmers' => Farmer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Sales Reports: Daily / Range sales + Monthly FY summary.
     */
    public function sales(Request $request)
    {
        $this->authorizePermission('reports.sales');

        $filters = $request->only(['preset', 'date_from', 'date_to', 'customer_id', 'status']);
        $invoices = $this->reporting->salesReport($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportSalesCsv($invoices);
        }

        $fyYear = (int) ($request->get('fy_year') ?? (now()->month >= 4 ? now()->year : now()->year - 1));
        $monthlySummary = $this->reporting->monthlySalesSummary($fyYear);

        return view('reports.sales', [
            'invoices' => $invoices,
            'monthlySummary' => $monthlySummary,
            'fyYear' => $fyYear,
            'filters' => $filters,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Inventory Reports: Current Stock Balance, Movements Register, Capacity Utilization & Low Stock Alert.
     */
    public function inventory(Request $request)
    {
        $this->authorizePermission('reports.inventory');

        $tab = $request->get('tab', 'balance');
        $filters = $request->only(['preset', 'date_from', 'date_to', 'warehouse_id', 'crop_id', 'type']);

        $stockBalances = $this->reporting->stockBalanceReport($filters);
        $movements = $tab === 'movements' ? $this->reporting->stockMovementRegister($filters) : collect();
        $capacity = $this->reporting->warehouseCapacityReport();
        $lowStock = $this->reporting->lowStockAlertReport();

        if ($request->get('export') === 'csv') {
            if ($tab === 'movements') {
                return $this->exportMovementsCsv($movements);
            }
            return $this->exportStockBalanceCsv($stockBalances);
        }

        return view('reports.inventory', [
            'tab' => $tab,
            'filters' => $filters,
            'stockBalances' => $stockBalances,
            'movements' => $movements,
            'capacity' => $capacity,
            'lowStock' => $lowStock,
            'warehouses' => Warehouse::where('is_active', true)->get(),
            'crops' => Crop::where('is_active', true)->get(),
        ]);
    }

    /**
     * Financial Reports: Farmer & Customer Outstanding Balances, Expense Summary, Statements, Crop Profitability.
     */
    public function finance(Request $request)
    {
        $this->authorizePermission('reports.finance');

        $tab = $request->get('tab', 'outstanding');
        $filters = $request->only(['preset', 'date_from', 'date_to', 'account_id']);

        $farmerOutstanding = $this->reporting->farmerOutstandingReport();
        $customerOutstanding = $this->reporting->customerOutstandingReport();
        $expenseSummary = $tab === 'expenses' ? $this->reporting->expenseSummary($filters) : collect();
        $cropProfitability = $tab === 'profitability' ? $this->reporting->cropProfitability($filters) : collect();

        $accountStatement = null;
        if ($tab === 'statement' && ! empty($filters['account_id'])) {
            $accountStatement = $this->reporting->accountStatement((int) $filters['account_id'], $filters);
        }

        if ($request->get('export') === 'csv') {
            if ($tab === 'customer_outstanding') {
                return $this->exportCustomerOutstandingCsv($customerOutstanding);
            }
            return $this->exportFarmerOutstandingCsv($farmerOutstanding);
        }

        return view('reports.finance', [
            'tab' => $tab,
            'filters' => $filters,
            'farmerOutstanding' => $farmerOutstanding,
            'customerOutstanding' => $customerOutstanding,
            'expenseSummary' => $expenseSummary,
            'cropProfitability' => $cropProfitability,
            'accountStatement' => $accountStatement,
            'accounts' => CashAccount::where('is_active', true)->get(),
        ]);
    }

    /**
     * Activity & Audit Reports: Cancelled Transactions, Growth, Audit Trail.
     */
    public function audit(Request $request)
    {
        $this->authorizePermission('reports.activity');

        $filters = $request->only(['preset', 'date_from', 'date_to']);
        $cancelledData = $this->reporting->cancelledTransactionsReport($filters);
        $farmerGrowth = $this->reporting->farmerRegistrationGrowth();

        return view('reports.audit', [
            'filters' => $filters,
            'cancelledData' => $cancelledData,
            'farmerGrowth' => $farmerGrowth,
        ]);
    }

    // --- CSV Streaming Helpers ---

    protected function exportPurchasesCsv($purchases): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="purchases-report-'.now()->format('YmdHis').'.csv"',
        ];

        return response()->stream(function () use ($purchases) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Purchase ID', 'Date', 'Farmer ID', 'Farmer Name', 'Village', 'Crop', 'Variety', 'Warehouse', 'Bags', 'Gross Wt (Qtl)', 'Net Wt (Qtl)', 'Rate/Qtl (₹)', 'Net Payable (₹)', 'Paid (₹)', 'Balance (₹)', 'Status']);

            foreach ($purchases as $p) {
                fputcsv($handle, [
                    $p->purchase_id,
                    $p->purchase_date->toDateString(),
                    $p->farmer?->farmer_id,
                    $p->farmer?->name,
                    $p->farmer?->village,
                    $p->crop?->name,
                    $p->variety?->name ?? 'Standard',
                    $p->warehouse?->name,
                    $p->bags,
                    $p->gross_weight_qtl,
                    $p->net_weight_qtl,
                    $p->rate_per_qtl,
                    $p->net_payable,
                    $p->paid_amount,
                    $p->balance_due,
                    $p->status,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function exportSalesCsv($invoices): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sales-invoices-report-'.now()->format('YmdHis').'.csv"',
        ];

        return response()->stream(function () use ($invoices) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice No', 'Date', 'Customer Name', 'GSTIN', 'Total Bags', 'Total Wt (Qtl)', 'Subtotal (₹)', 'Tax (₹)', 'Net Amount (₹)', 'Received (₹)', 'Balance Due (₹)', 'Status']);

            foreach ($invoices as $inv) {
                $totalBags = $inv->items->sum('bags');
                $totalQtl = $inv->items->sum('weight_qtl');

                fputcsv($handle, [
                    $inv->invoice_number,
                    $inv->invoice_date->toDateString(),
                    $inv->customer?->name,
                    $inv->customer?->gstin ?? 'Unregistered',
                    $totalBags,
                    $totalQtl,
                    $inv->subtotal,
                    $inv->tax_amount,
                    $inv->net_amount,
                    $inv->received_amount,
                    $inv->balance_due,
                    $inv->status,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function exportStockBalanceCsv($balances): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="stock-balance-'.now()->format('YmdHis').'.csv"',
        ];

        return response()->stream(function () use ($balances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Warehouse', 'Crop', 'Variety', 'Stock Bags', 'Net Stock (Qtl)', 'Net Stock (MT)']);

            foreach ($balances as $b) {
                fputcsv($handle, [
                    $b['warehouse']?->name,
                    $b['crop']?->name,
                    $b['variety']?->name ?? 'Standard Grade',
                    $b['bags'],
                    $b['weight_qtl'],
                    $b['weight_mt'],
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function exportMovementsCsv($movements): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="stock-movements-'.now()->format('YmdHis').'.csv"',
        ];

        return response()->stream(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Warehouse', 'Crop', 'Variety', 'Bags In', 'Bags Out', 'Weight In (Qtl)', 'Weight Out (Qtl)', 'Reference Doc']);

            foreach ($movements as $m) {
                fputcsv($handle, [
                    $m->transaction_date->toDateString(),
                    $m->transaction_type,
                    $m->warehouse?->name,
                    $m->crop?->name,
                    $m->variety?->name ?? 'Standard',
                    $m->bags_in,
                    $m->bags_out,
                    $m->weight_qtl_in,
                    $m->weight_qtl_out,
                    $m->reference_type.' #'.$m->reference_id,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function exportFarmerOutstandingCsv($records): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="farmer-outstanding-'.now()->format('YmdHis').'.csv"',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Farmer ID', 'Farmer Name', 'Mobile', 'Village', 'Tehsil', 'District', 'Unpaid Purchases', 'Total Outstanding (₹)', '0-15 Days', '16-30 Days', '31-60 Days', '60+ Days']);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r['farmer']->farmer_id,
                    $r['farmer']->name,
                    $r['farmer']->mobile,
                    $r['farmer']->village,
                    $r['farmer']->tehsil,
                    $r['farmer']->district,
                    $r['purchase_count'],
                    $r['total_outstanding'],
                    $r['aging']['0_15'],
                    $r['aging']['16_30'],
                    $r['aging']['31_60'],
                    $r['aging']['60_plus'],
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function exportCustomerOutstandingCsv($records): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="customer-outstanding-'.now()->format('YmdHis').'.csv"',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Customer Name', 'Contact Person', 'Phone', 'Credit Limit (₹)', 'Overdue Invoices', 'Total Due (₹)', 'Credit Utilization (%)', '0-15 Days', '16-30 Days', '31-60 Days', '60+ Days']);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r['customer']->name,
                    $r['customer']->contact_person,
                    $r['customer']->phone,
                    $r['credit_limit'],
                    $r['invoice_count'],
                    $r['total_outstanding'],
                    $r['credit_utilization'] ?? 'N/A',
                    $r['aging']['0_15'],
                    $r['aging']['16_30'],
                    $r['aging']['31_60'],
                    $r['aging']['60_plus'],
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    protected function authorizePermission(string $permission): void
    {
        if (! auth()->user()->hasPermission($permission)) {
            abort(403, 'You do not have permission to view this report.');
        }
    }
}
