<?php

namespace App\Http\Controllers;

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
use App\Models\SalesOrder;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WebsiteInquiry;
use App\Services\CropPriceTrendService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CropPriceTrendService $trendService): View
    {
        $user = $request->user();
        $today = now()->toDateString();
        $todayCarbon = now()->startOfDay();

        // 1. Core KPIs: Today's Purchases & Sales
        $todayPurchases = Purchase::whereDate('purchase_date', $today)
            ->where('status', Purchase::STATUS_FINALIZED)
            ->get();
        $todayPurchasesCount = $todayPurchases->count();
        $todayPurchasesQtl = $todayPurchases->sum('net_weight_qtl');
        $todayPurchasesAmount = $todayPurchases->sum('net_payable_amount');

        $todaySales = SalesInvoice::with('items')
            ->whereDate('invoice_date', $today)
            ->where('status', SalesInvoice::STATUS_FINALIZED)
            ->get();
        $todaySalesCount = $todaySales->count();
        $todaySalesAmount = $todaySales->sum('total_amount');
        $todaySalesQtl = $todaySales->sum(fn ($inv) => (float) $inv->totalQuantityQtl());

        // Network Counts
        $activeFarmersCount = Farmer::where('is_active', true)->count();
        $activeCustomersCount = Customer::where('is_active', true)->count();

        // Action / Draft Alerts
        $draftPurchasesCount = Purchase::where('status', Purchase::STATUS_DRAFT)->count();
        $draftSalesCount = SalesInvoice::where('status', SalesInvoice::STATUS_DRAFT)->count();
        $draftOrdersCount = SalesOrder::where('status', 'draft')->count();
        $pendingAdjustmentsCount = StockAdjustment::where('status', StockAdjustment::STATUS_PENDING)->count();
        $newInquiriesCount = WebsiteInquiry::where('status', WebsiteInquiry::STATUS_NEW)->count();

        // Stock on hand & Warehouse Capacity
        $totalStockQtl = InventoryTransaction::sum('quantity_qtl') ?? '0';
        $totalStockQtl = max(0, (float) $totalStockQtl);
        $totalStockBags = (int) round($totalStockQtl * 2);

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stockByWarehouse = InventoryTransaction::select('warehouse_id', DB::raw('SUM(quantity_qtl) as qtl'))
            ->groupBy('warehouse_id')
            ->pluck('qtl', 'warehouse_id');

        $warehouseStats = $warehouses->map(function (Warehouse $wh) use ($stockByWarehouse) {
            $stock = max(0.0, (float) ($stockByWarehouse[$wh->id] ?? 0));
            $capacity = (float) ($wh->capacity_quintals ?? 0);
            $pct = $capacity > 0 ? min(100, round(($stock / $capacity) * 100, 1)) : null;

            return [
                'warehouse' => $wh,
                'stock_qtl' => $stock,
                'capacity_qtl' => $capacity,
                'utilization_pct' => $pct,
                'is_transit' => strtoupper((string) $wh->code) === 'WH-DIRECT',
            ];
        });

        // Accounts, Liquidity & Cashflow
        $totalLiquidity = CashAccount::where('is_active', true)->sum('current_balance') ?? 0;
        $todayExpenses = Expense::whereDate('expense_date', $today)->where('status', Expense::STATUS_APPROVED)->sum('amount') ?? 0;
        $todayReceipts = CustomerReceipt::whereDate('receipt_date', $today)->whereNull('reversed_at')->sum('amount') ?? 0;
        $todayDisbursements = PurchasePayment::whereDate('payment_date', $today)
            ->whereNull('reversed_at')
            ->get()
            ->sum(fn (PurchasePayment $p) => (float) $p->effective_payout);

        $farmerOutstandingTotal = Purchase::where('status', Purchase::STATUS_FINALIZED)->where('balance_due', '>', 0)->sum('balance_due');
        $customerReceivablesQuery = SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->where('balance_due', '>', 0);
        $customerReceivablesTotal = (clone $customerReceivablesQuery)->sum('balance_due');
        $unpaidInvoicesCount = (clone $customerReceivablesQuery)->count();

        // Delayed Payment Bonus Metrics & Live Accrual per Farmer
        $totalBonusPaid = (float) (PurchasePayment::whereNull('reversed_at')->sum('bonus_amount') ?? 0);
        $pendingDelayedPurchases = Purchase::with(['farmer', 'crop'])
            ->where('status', Purchase::STATUS_FINALIZED)
            ->where('balance_due', '>', 0)
            ->whereDate('purchase_date', '<', $today)
            ->orderBy('purchase_date')
            ->get();

        $estimatedAccruedBonus = 0.0;
        $delayedPurchasesList = collect();

        foreach ($pendingDelayedPurchases as $dp) {
            $days = (int) max(0, Carbon::parse($dp->purchase_date)->startOfDay()->diffInDays($todayCarbon, false));
            $enabled = $dp->farmer ? $dp->farmer->isDelayedBonusEnabled() : (setting('procurement.delayed_bonus_enabled', '1') == '1');
            $rate = $dp->farmer ? (float) $dp->farmer->effective_bonus_rate : (float) setting('procurement.delayed_bonus_rate', '1.00');
            $mode = $dp->farmer ? $dp->farmer->effective_bonus_mode : (string) setting('procurement.delayed_bonus_mode', 'monthly');

            $accrued = 0.0;
            if ($enabled && $days > 0 && $rate > 0) {
                if ($mode === 'monthly') {
                    $accrued = round(((float) $dp->balance_due) * ($rate / 100) * ($days / 30), 2);
                } elseif ($mode === 'flat') {
                    $accrued = round(((float) $dp->balance_due) * ($rate / 100), 2);
                }
            }
            $estimatedAccruedBonus += $accrued;

            if ($delayedPurchasesList->count() < 5) {
                $delayedPurchasesList->push([
                    'purchase' => $dp,
                    'days_delayed' => $days,
                    'bonus_enabled' => $enabled,
                    'bonus_rate' => number_format($rate, 2, '.', ''),
                    'bonus_mode' => $mode,
                    'is_custom' => $dp->farmer ? $dp->farmer->hasCustomBonusSetting() : false,
                    'accrued_bonus' => $accrued,
                    'est_payout' => round(((float) $dp->balance_due) + $accrued, 2),
                ]);
            }
        }
        $delayedPurchasesCount = $pendingDelayedPurchases->count();

        // Recent Dispatches & Transport Challans (Sales Invoices + Stock Transfers)
        $recentInvoiceDispatches = SalesInvoice::with(['customer', 'items'])
            ->where('status', SalesInvoice::STATUS_FINALIZED)
            ->latest('invoice_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (SalesInvoice $inv) => [
                'type' => 'sale',
                'date' => $inv->invoice_date,
                'doc_number' => $inv->invoice_number,
                'challan_number' => $inv->challanNumber(),
                'party_or_route' => $inv->customer?->name ?? '-',
                'vehicle' => $inv->vehicle_number,
                'qty_qtl' => $inv->totalQuantityQtl(),
                'doc_url' => route('sales-invoices.show', $inv),
                'challan_url' => route('sales-invoices.challan', $inv),
            ]);

        $recentTransferDispatches = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'crop'])
            ->latest('transfer_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (StockTransfer $trf) => [
                'type' => 'transfer',
                'date' => $trf->transfer_date,
                'doc_number' => $trf->transfer_number,
                'challan_number' => $trf->challanNumber(),
                'party_or_route' => ($trf->fromWarehouse?->code ?? '?') . ' → ' . ($trf->toWarehouse?->code ?? '?'),
                'vehicle' => $trf->vehicle_number,
                'qty_qtl' => $trf->quantity_qtl,
                'doc_url' => route('stock-transfers.show', $trf),
                'challan_url' => route('stock-transfers.challan', $trf),
            ]);

        $recentChallans = $recentInvoiceDispatches
            ->concat($recentTransferDispatches)
            ->sortByDesc(fn ($row) => $row['date']?->timestamp ?? 0)
            ->take(5)
            ->values();

        // Recent Finalized Weighbridge Purchases
        $recentPurchases = Purchase::with(['farmer', 'crop'])
            ->where('status', Purchase::STATUS_FINALIZED)
            ->latest('purchase_date')
            ->latest('id')
            ->limit(5)
            ->get();

        // Active Crops, Live Stock Map, Low Stock Alerts & Live Mandi Crop Purchase Rates
        $lowStockThreshold = (float) setting('inventory.low_stock_threshold_qtl', 10);
        $crops = Crop::where('is_active', true)->orderBy('name')->get();
        $stockByCropMap = InventoryTransaction::select('crop_id', DB::raw('SUM(quantity_qtl) as qtl'))
            ->groupBy('crop_id')
            ->pluck('qtl', 'crop_id');

        $lowStockCrops = collect();
        $stockByCropLabels = [];
        $stockByCropData = [];

        foreach ($crops as $c) {
            $cropStock = max(0.0, (float) ($stockByCropMap[$c->id] ?? 0));
            if ($cropStock < $lowStockThreshold) {
                $lowStockCrops->push(['crop' => $c, 'stock_qtl' => $cropStock]);
            }
            if ($cropStock > 0) {
                $stockByCropLabels[] = $c->name;
                $stockByCropData[] = round($cropStock, 2);
            }
        }

        // Build Live Mandi Buying Rates + Price Diff & 90-day Sparkline via CropPriceTrendService
        $trendsByCropId = collect($trendService->build($crops))->keyBy('id');
        $mandiRateCards = $crops->map(function (Crop $crop) use ($trendsByCropId, $stockByCropMap) {
            $trend = $trendsByCropId->get($crop->id, []);
            $hasRate = $crop->current_purchase_rate !== null && (float) $crop->current_purchase_rate > 0;

            return [
                'crop' => $crop,
                'has_rate' => $hasRate,
                'formatted_rate' => $crop->formattedPurchaseRate(false),
                'unit_label' => $crop->shortUnitLabel(),
                'is_public' => $hasRate && (bool) $crop->show_rate_on_website,
                'stock_qtl' => max(0.0, (float) ($stockByCropMap[$crop->id] ?? 0)),
                'trend' => $trend,
            ];
        });
        $latestRateUpdatedAt = $crops->whereNotNull('rate_updated_at')->max('rate_updated_at');

        // 2. Chart Data: Last 6 Months Purchases vs Sales
        $chartLabels = [];
        $chartPurchases = [];
        $chartSales = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $mStart = $month->copy()->startOfMonth()->toDateString();
            $mEnd = $month->copy()->endOfMonth()->toDateString();

            $chartLabels[] = $month->format('M Y');
            $chartPurchases[] = (float) Purchase::where('status', Purchase::STATUS_FINALIZED)
                ->whereBetween('purchase_date', [$mStart, $mEnd])
                ->sum('net_payable_amount');
            $chartSales[] = (float) SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)
                ->whereBetween('invoice_date', [$mStart, $mEnd])
                ->sum('total_amount');
        }

        // Top 5 Farmers by Purchase Volume
        $topFarmers = Purchase::where('status', Purchase::STATUS_FINALIZED)
            ->select('farmer_id', DB::raw('SUM(net_weight_qtl) as total_qtl'), DB::raw('SUM(net_payable_amount) as total_amount'))
            ->groupBy('farmer_id')
            ->orderByDesc('total_qtl')
            ->take(5)
            ->with('farmer')
            ->get();

        // Top 5 Customers by Revenue
        $topCustomers = SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)
            ->select('customer_id', DB::raw('SUM(total_amount) as total_revenue'))
            ->groupBy('customer_id')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->with('customer')
            ->get();

        // Recent Activity
        $recentActivity = AuditLog::with('user')->latest('id')->limit(8)->get();

        // Setup checklist for Admin
        $setup = [];
        $userStats = [];
        if ($user->hasPermission('settings.manage')) {
            $ratesPublished = $crops->contains(
                fn (Crop $c) => $c->show_rate_on_website && (float) $c->current_purchase_rate > 0
            );
            $setup = [
                [__('dashboard.setup_business_address'), filled(setting('business.address')), route('settings.edit', ['tab' => 'business'])],
                [__('dashboard.setup_business_phone'), filled(setting('business.phone')), route('settings.edit', ['tab' => 'business'])],
                [__('dashboard.setup_logo_uploaded'), filled(setting('business.logo')), route('settings.edit', ['tab' => 'business'])],
                [__('dashboard.setup_staff_created'), User::count() > 1, route('users.index')],
                [__('dashboard.setup_bonus_configured'), setting('procurement.delayed_bonus_enabled', '1') == '1', route('settings.edit', ['tab' => 'system'])],
                [__('dashboard.setup_crop_rates_published'), $ratesPublished, $user->hasPermission('website.manage') ? route('website.admin.index') : route('crops.index')],
            ];
            $userStats = [
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'failedLogins' => AuditLog::where('action', 'auth.login_failed')->where('created_at', '>=', now()->subDay())->count(),
            ];
        }

        return view('dashboard', [
            'user' => $user,
            'todayPurchasesCount' => $todayPurchasesCount,
            'todayPurchasesQtl' => $todayPurchasesQtl,
            'todayPurchasesAmount' => $todayPurchasesAmount,
            'todaySalesCount' => $todaySalesCount,
            'todaySalesAmount' => $todaySalesAmount,
            'todaySalesQtl' => $todaySalesQtl,
            'activeFarmersCount' => $activeFarmersCount,
            'activeCustomersCount' => $activeCustomersCount,
            'draftPurchasesCount' => $draftPurchasesCount,
            'draftSalesCount' => $draftSalesCount,
            'draftOrdersCount' => $draftOrdersCount,
            'pendingAdjustmentsCount' => $pendingAdjustmentsCount,
            'newInquiriesCount' => $newInquiriesCount,
            'totalStockQtl' => $totalStockQtl,
            'totalStockBags' => $totalStockBags,
            'warehouseStats' => $warehouseStats,
            'totalLiquidity' => $totalLiquidity,
            'todayExpenses' => $todayExpenses,
            'todayReceipts' => $todayReceipts,
            'todayDisbursements' => $todayDisbursements,
            'farmerOutstandingTotal' => $farmerOutstandingTotal,
            'customerReceivablesTotal' => $customerReceivablesTotal,
            'unpaidInvoicesCount' => $unpaidInvoicesCount,
            'totalBonusPaid' => $totalBonusPaid,
            'estimatedAccruedBonus' => $estimatedAccruedBonus,
            'delayedPurchasesCount' => $delayedPurchasesCount,
            'delayedPurchasesList' => $delayedPurchasesList,
            'recentChallans' => $recentChallans,
            'recentPurchases' => $recentPurchases,
            'mandiRateCards' => $mandiRateCards,
            'latestRateUpdatedAt' => $latestRateUpdatedAt,
            'lowStockThreshold' => $lowStockThreshold,
            'lowStockCrops' => $lowStockCrops,
            'chartLabels' => $chartLabels,
            'chartPurchases' => $chartPurchases,
            'chartSales' => $chartSales,
            'sixMonthPurchasesTotal' => array_sum($chartPurchases),
            'sixMonthSalesTotal' => array_sum($chartSales),
            'stockByCropLabels' => $stockByCropLabels,
            'stockByCropData' => $stockByCropData,
            'topFarmers' => $topFarmers,
            'topCustomers' => $topCustomers,
            'recentActivity' => $recentActivity,
            'cashAccounts' => CashAccount::where('is_active', true)->get(),
            'setup' => $setup,
            'userStats' => $userStats,
        ]);
    }
}
