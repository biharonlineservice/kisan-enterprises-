<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $flow = $request->query('flow'); // 'increase' or 'decrease'
        $period = $request->query('period'); // 'today', 'month'
        $search = trim((string) $request->query('q', ''));

        $adjustments = StockAdjustment::with(['warehouse', 'crop', 'cropVariety', 'creator', 'approver'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($flow === 'increase', fn ($q) => $q->where('quantity_kg', '>', 0))
            ->when($flow === 'decrease', fn ($q) => $q->where('quantity_kg', '<', 0))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('crop_id'), fn ($q) => $q->where('crop_id', $request->crop_id))
            ->when($request->filled('type'), fn ($q) => $q->where('adjustment_type', $request->type))
            ->when($period === 'today', fn ($q) => $q->whereDate('adjustment_date', Carbon::today()))
            ->when($period === 'month', fn ($q) => $q->where('adjustment_date', '>=', Carbon::now()->startOfMonth()))
            ->when($request->filled('from'), fn ($q) => $q->where('adjustment_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('adjustment_date', '<=', Carbon::parse($request->to)->endOfDay()))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('adjustment_number', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('crop', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('creator', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('approver', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalCount = StockAdjustment::count();
        $pendingCount = StockAdjustment::where('status', StockAdjustment::STATUS_PENDING)->count();
        $approvedCount = StockAdjustment::where('status', StockAdjustment::STATUS_APPROVED)->count();
        $rejectedCount = StockAdjustment::where('status', StockAdjustment::STATUS_REJECTED)->count();
        $increaseCount = StockAdjustment::where('quantity_kg', '>', 0)->count();
        $decreaseCount = StockAdjustment::where('quantity_kg', '<', 0)->count();

        // Approved volume metrics
        $approvedAdjustments = StockAdjustment::where('status', StockAdjustment::STATUS_APPROVED)->get(['quantity_kg', 'quantity_qtl']);
        $netApprovedQtl = 0.0;
        $approvedIncreaseQtl = 0.0;
        $approvedIncreaseKg = 0.0;
        $approvedDecreaseQtl = 0.0;
        $approvedDecreaseKg = 0.0;

        foreach ($approvedAdjustments as $item) {
            $qtl = (float) $item->quantity_qtl;
            $kg = (float) $item->quantity_kg;
            $netApprovedQtl += $qtl;
            if ($kg > 0) {
                $approvedIncreaseQtl += $qtl;
                $approvedIncreaseKg += $kg;
            } else {
                $approvedDecreaseQtl += abs($qtl);
                $approvedDecreaseKg += abs($kg);
            }
        }

        // Pending volume metrics
        $pendingAdjustments = StockAdjustment::where('status', StockAdjustment::STATUS_PENDING)->get(['quantity_kg', 'quantity_qtl']);
        $pendingQtl = 0.0;
        $pendingKg = 0.0;
        foreach ($pendingAdjustments as $item) {
            $pendingQtl += abs((float) $item->quantity_qtl);
            $pendingKg += abs((float) $item->quantity_kg);
        }

        $counts = [
            'all' => $totalCount,
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'rejected' => $rejectedCount,
            'increase' => $increaseCount,
            'decrease' => $decreaseCount,
        ];

        $kpis = [
            'netApprovedQtl' => $netApprovedQtl,
            'netApprovedBags' => (int) round(($approvedIncreaseKg - $approvedDecreaseKg) / 50),
            'pendingCount' => $pendingCount,
            'pendingQtl' => $pendingQtl,
            'pendingBags' => (int) round($pendingKg / 50),
            'approvedIncreaseQtl' => $approvedIncreaseQtl,
            'approvedIncreaseBags' => (int) round($approvedIncreaseKg / 50),
            'approvedDecreaseQtl' => $approvedDecreaseQtl,
            'approvedDecreaseBags' => (int) round($approvedDecreaseKg / 50),
            'totalCount' => $totalCount,
        ];

        return view('inventory.adjustments.index', [
            'adjustments' => $adjustments,
            'counts' => $counts,
            'kpis' => $kpis,
            'currentStatus' => $status,
            'currentFlow' => $flow,
            'currentPeriod' => $period,
            'search' => $search,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::where('is_active', true)->orderBy('name')->get(),
            'types' => StockAdjustment::TYPES,
        ]);
    }

    public function create(): View
    {
        return view('inventory.adjustments.form', [
            'adjustment' => new StockAdjustment([
                'adjustment_date' => now(),
                'adjustment_type' => 'shrinkage',
            ]),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::with('varieties')->where('is_active', true)->orderBy('name')->get(),
            'types' => StockAdjustment::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'crop_id' => ['required', 'exists:crops,id'],
            'crop_variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'adjustment_type' => ['required', 'in:' . implode(',', array_keys(StockAdjustment::TYPES))],
            'quantity_kg' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $qtyKg = (float) $data['quantity_kg'];
        if ($request->input('direction') === 'decrease' && $qtyKg > 0) {
            $data['quantity_kg'] = -1 * $qtyKg;
        } elseif ($request->input('direction') === 'increase' && $qtyKg < 0) {
            $data['quantity_kg'] = abs($qtyKg);
        }

        try {
            $adj = $this->inventory->requestAdjustment($data, $request->user()->id);

            return redirect()->route('stock-adjustments.show', $adj)
                ->with('success', "Stock adjustment {$adj->adjustment_number} submitted for approval.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load(['warehouse', 'crop', 'cropVariety', 'creator', 'approver']);

        return view('inventory.adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function approve(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        abort_unless($stockAdjustment->isPending(), 400, 'Only pending adjustments can be approved.');

        try {
            $this->inventory->approveAdjustment($stockAdjustment, $request->user()->id);

            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('success', "Stock adjustment {$stockAdjustment->adjustment_number} approved and inventory updated.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        abort_unless($stockAdjustment->isPending(), 400, 'Only pending adjustments can be rejected.');

        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        try {
            $this->inventory->rejectAdjustment($stockAdjustment, $request->reason, $request->user()->id);

            return redirect()->route('stock-adjustments.show', $stockAdjustment)
                ->with('success', "Stock adjustment {$stockAdjustment->adjustment_number} rejected.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
