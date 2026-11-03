<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    /**
     * Display stock summary per warehouse and crop.
     */
    public function index(Request $request): View
    {
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;
        $stockSummary = $this->inventory->getStockSummary($warehouseId);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $crops = Crop::get()->keyBy('id');

        // Calculate totals across visible summary
        $totalStockQtl = '0.000';
        $lowStockCount = 0;
        $estimatedValuation = '0.00';
        $totalCapacityQtl = $warehouses->sum('capacity_qtl');

        foreach ($stockSummary as $item) {
            $totalStockQtl = bcadd($totalStockQtl, (string) $item->total_qtl, 3);
            if ($item->is_low_stock) {
                $lowStockCount++;
            }
            $crop = $crops->get($item->crop_id);
            $rate = $crop?->current_purchase_rate ?? '0.00';
            $itemVal = bcmul((string) $item->total_qtl, (string) $rate, 2);
            $item->estimated_value = $itemVal;
            $item->rate = $rate;
            $estimatedValuation = bcadd($estimatedValuation, $itemVal, 2);
        }

        // Compute per-warehouse totals for quick-filter tabs
        $allStock = $warehouseId ? $this->inventory->getStockSummary(null) : $stockSummary;
        $warehouseStockTotals = [];
        foreach ($allStock as $row) {
            $warehouseStockTotals[$row->warehouse_id] = bcadd(
                $warehouseStockTotals[$row->warehouse_id] ?? '0.000',
                (string) $row->total_qtl,
                3
            );
        }

        // Apply search / status filters
        if ($request->filled('q')) {
            $term = mb_strtolower(trim($request->q));
            $stockSummary = $stockSummary->filter(function ($row) use ($term) {
                return str_contains(mb_strtolower($row->crop_name), $term)
                    || str_contains(mb_strtolower($row->crop_code), $term)
                    || str_contains(mb_strtolower($row->warehouse_name), $term)
                    || str_contains(mb_strtolower($row->warehouse_code), $term)
                    || ($row->variety_name && str_contains(mb_strtolower($row->variety_name), $term));
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'low_stock') {
                $stockSummary = $stockSummary->filter(fn ($row) => $row->is_low_stock);
            } elseif ($request->status === 'in_stock') {
                $stockSummary = $stockSummary->filter(fn ($row) => ! $row->is_low_stock);
            }
        }

        return view('inventory.index', [
            'stockSummary' => $stockSummary,
            'warehouses' => $warehouses,
            'selectedWarehouseId' => $warehouseId,
            'totalStockQtl' => $totalStockQtl,
            'lowStockCount' => $lowStockCount,
            'estimatedValuation' => $estimatedValuation,
            'totalCapacityQtl' => $totalCapacityQtl,
            'warehouseStockTotals' => $warehouseStockTotals,
        ]);
    }

    /**
     * Display immutable ledger of all stock movements.
     */
    public function movements(Request $request): View
    {
        $query = InventoryTransaction::with(['warehouse', 'crop', 'cropVariety', 'creator']);

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_number', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('crop', fn ($cq) => $cq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
                    ->orWhereHas('warehouse', fn ($wq) => $wq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
                    ->orWhereHas('creator', fn ($uq) => $uq->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('source_type')) {
            if ($request->source_type === 'sale') {
                $query->whereIn('source_type', ['sale', 'sales_invoice']);
            } else {
                $query->where('source_type', $request->source_type);
            }
        }

        if ($request->filled('from')) {
            $query->where('transaction_date', '>=', Carbon::parse($request->from)->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('transaction_date', '<=', Carbon::parse($request->to)->endOfDay());
        }

        // Base KPI clone before pagination
        $kpiBase = clone $query;

        $inwardSum = (clone $kpiBase)->where('direction', 'in')->sum('quantity_qtl');
        $totalInwardQtl = (string) ($inwardSum ?? '0.000');
        $inwardCount = (clone $kpiBase)->where('direction', 'in')->count();
        $inwardBags = (int) round(((float) $totalInwardQtl) * 2);

        $outwardSum = (clone $kpiBase)->where('direction', 'out')->sum('quantity_qtl');
        $totalOutwardQtl = (string) abs((float) ($outwardSum ?? '0.000'));
        $outwardCount = (clone $kpiBase)->where('direction', 'out')->count();
        $outwardBags = (int) round(((float) $totalOutwardQtl) * 2);

        $netFlowQtl = bcsub($totalInwardQtl, $totalOutwardQtl, 3);
        $netFlowBags = (int) round(((float) $netFlowQtl) * 2);
        $totalCount = (clone $kpiBase)->count();

        // Today's summary for banner chips
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $todayInward = (string) (InventoryTransaction::where('direction', 'in')
            ->whereBetween('transaction_date', [$todayStart, $todayEnd])
            ->sum('quantity_qtl') ?? '0.000');
        $todayOutward = (string) abs((float) (InventoryTransaction::where('direction', 'out')
            ->whereBetween('transaction_date', [$todayStart, $todayEnd])
            ->sum('quantity_qtl') ?? '0.000'));
        $todayCount = InventoryTransaction::whereBetween('transaction_date', [$todayStart, $todayEnd])->count();

        // Counts for tab filters (independent of direction/source_type, respecting search/warehouse/crop/dates)
        $tabBase = InventoryTransaction::query();
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $tabBase->where(function ($sub) use ($q) {
                $sub->where('reference_number', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('crop', fn ($cq) => $cq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
                    ->orWhereHas('warehouse', fn ($wq) => $wq->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('warehouse_id')) {
            $tabBase->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('crop_id')) {
            $tabBase->where('crop_id', $request->crop_id);
        }
        if ($request->filled('from')) {
            $tabBase->where('transaction_date', '>=', Carbon::parse($request->from)->startOfDay());
        }
        if ($request->filled('to')) {
            $tabBase->where('transaction_date', '<=', Carbon::parse($request->to)->endOfDay());
        }

        $tabCounts = [
            'all' => (clone $tabBase)->count(),
            'in' => (clone $tabBase)->where('direction', 'in')->count(),
            'out' => (clone $tabBase)->where('direction', 'out')->count(),
            'purchase' => (clone $tabBase)->where('source_type', 'purchase')->count(),
            'sale' => (clone $tabBase)->whereIn('source_type', ['sale', 'sales_invoice'])->count(),
            'stock_transfer' => (clone $tabBase)->where('source_type', 'stock_transfer')->count(),
            'stock_adjustment' => (clone $tabBase)->where('source_type', 'stock_adjustment')->count(),
        ];

        $transactions = $query
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('inventory.movements', [
            'transactions' => $transactions,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::where('is_active', true)->orderBy('name')->get(),
            'totalInwardQtl' => $totalInwardQtl,
            'inwardBags' => $inwardBags,
            'inwardCount' => $inwardCount,
            'totalOutwardQtl' => $totalOutwardQtl,
            'outwardBags' => $outwardBags,
            'outwardCount' => $outwardCount,
            'netFlowQtl' => $netFlowQtl,
            'netFlowBags' => $netFlowBags,
            'totalCount' => $totalCount,
            'todayInward' => $todayInward,
            'todayOutward' => $todayOutward,
            'todayCount' => $todayCount,
            'tabCounts' => $tabCounts,
        ]);
    }
}
