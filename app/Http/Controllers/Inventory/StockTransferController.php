<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'crop', 'cropVariety', 'creator']);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->q).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('transfer_number', 'like', $term)
                    ->orWhere('vehicle_number', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('crop', fn ($cq) => $cq->where('name', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('fromWarehouse', fn ($wq) => $wq->where('name', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('toWarehouse', fn ($wq) => $wq->where('name', 'like', $term)->orWhere('code', 'like', $term))
                    ->orWhereHas('creator', fn ($uq) => $uq->where('name', 'like', $term));
            });
        }

        if ($request->filled('crop_id')) {
            $query->where('crop_id', $request->crop_id);
        }

        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }

        if ($request->filled('from')) {
            $query->where('transfer_date', '>=', Carbon::parse($request->from)->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('transfer_date', '<=', Carbon::parse($request->to)->endOfDay());
        }

        if ($request->filled('segment')) {
            if ($request->segment === 'today') {
                $query->whereBetween('transfer_date', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()]);
            } elseif ($request->segment === 'month') {
                $query->whereBetween('transfer_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            }
        }

        // Base KPI calculations
        $kpiBase = clone $query;
        $totalQuantityQtl = (string) ($kpiBase->sum('quantity_qtl') ?? '0.000');
        $totalQuantityKg = (string) ($kpiBase->sum('quantity_kg') ?? '0.000');
        $totalTransfersCount = $kpiBase->count();
        $totalBags = (int) round(((float) $totalQuantityQtl) * 2);

        // Today's summary for banner chips
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $todayTransfersCount = StockTransfer::whereBetween('transfer_date', [$todayStart, $todayEnd])->count();
        $todayTransfersQtl = (string) (StockTransfer::whereBetween('transfer_date', [$todayStart, $todayEnd])->sum('quantity_qtl') ?? '0.000');

        $activeGodownsCount = Warehouse::where('is_active', true)->count();

        // Counts for quick tab filter pills
        $tabCounts = [
            'all' => StockTransfer::count(),
            'today' => StockTransfer::whereBetween('transfer_date', [$todayStart, $todayEnd])->count(),
            'month' => StockTransfer::whereBetween('transfer_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count(),
        ];

        $transfers = $query
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.transfers.index', [
            'transfers' => $transfers,
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::where('is_active', true)->orderBy('name')->get(),
            'totalQuantityQtl' => $totalQuantityQtl,
            'totalQuantityKg' => $totalQuantityKg,
            'totalTransfersCount' => $totalTransfersCount,
            'totalBags' => $totalBags,
            'todayTransfersCount' => $todayTransfersCount,
            'todayTransfersQtl' => $todayTransfersQtl,
            'activeGodownsCount' => $activeGodownsCount,
            'tabCounts' => $tabCounts,
        ]);
    }

    public function create(): View
    {
        return view('inventory.transfers.form', [
            'transfer' => new StockTransfer([
                'transfer_date' => now(),
            ]),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::with('varieties')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transfer_date' => ['required', 'date'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id'],
            'crop_id' => ['required', 'exists:crops,id'],
            'crop_variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'quantity_kg' => ['required', 'numeric', 'min:0.001', 'max:1000000'],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'from_warehouse_id.different' => 'Destination warehouse must be different from source warehouse.',
        ]);

        try {
            $transfer = $this->inventory->transfer($data, $request->user()->id);

            return redirect()->route('stock-transfers.show', $transfer)
                ->with('success', "Stock transfer {$transfer->transfer_number} processed successfully.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(StockTransfer $transfer): View
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'crop', 'cropVariety', 'creator']);

        return view('inventory.transfers.show', compact('transfer'));
    }

    public function challan(StockTransfer $transfer): View
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'crop', 'cropVariety', 'creator']);

        return view('inventory.transfers.challan', ['transfer' => $transfer]);
    }

    public function challanPdf(StockTransfer $transfer): Response
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'crop', 'cropVariety', 'creator']);

        $pdf = Pdf::loadView('inventory.transfers.challan', ['transfer' => $transfer, 'isPdf' => true])
            ->setPaper('a4', 'portrait');

        $challanNumber = $transfer->challanNumber();
        $filename = 'Transport_Challan_' . str_replace(['/', '\\'], '_', $challanNumber) . '.pdf';

        return $pdf->download($filename);
    }
}
