<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $stocks = InventoryTransaction::groupBy('warehouse_id')
            ->selectRaw('warehouse_id, SUM(quantity_qtl) as stock_qtl')
            ->pluck('stock_qtl', 'warehouse_id');

        $query = Warehouse::with('supervisor')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            });

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'with_stock') {
            $warehouseIdsWithStock = $stocks->filter(fn ($qty) => (float) $qty > 0)->keys();
            $query->whereIn('id', $warehouseIdsWithStock);
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $warehouses = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $totalCapacity = (float) Warehouse::where('is_active', true)->sum('capacity_quintals');
        $currentStockHeld = (float) InventoryTransaction::sum('quantity_qtl');
        $overallOccupancyPct = $totalCapacity > 0 ? min(100, round(($currentStockHeld / $totalCapacity) * 100, 1)) : 0;
        $withStockCount = $stocks->filter(fn ($qty) => (float) $qty > 0)->count();

        $metrics = [
            'totalWarehouses' => Warehouse::count(),
            'activeWarehouses' => Warehouse::where('is_active', true)->count(),
            'inactiveWarehouses' => Warehouse::where('is_active', false)->count(),
            'totalCapacity' => $totalCapacity,
            'currentStockHeld' => $currentStockHeld,
            'overallOccupancyPct' => $overallOccupancyPct,
            'withStockCount' => $withStockCount,
        ];

        return view('masters.warehouses.index', compact('warehouses', 'metrics', 'tab', 'stocks'));
    }

    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'all');

        $stocks = InventoryTransaction::groupBy('warehouse_id')
            ->selectRaw('warehouse_id, SUM(quantity_qtl) as stock_qtl')
            ->pluck('stock_qtl', 'warehouse_id');

        $query = Warehouse::with('supervisor')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            });

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'with_stock') {
            $warehouseIdsWithStock = $stocks->filter(fn ($qty) => (float) $qty > 0)->keys();
            $query->whereIn('id', $warehouseIdsWithStock);
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $warehouses = $query->orderBy('name')->get();
        $filename = 'warehouses-master-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($warehouses, $stocks) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, [
                'ID',
                'Warehouse Name',
                'Code',
                'Location Address',
                'Storage Capacity (Qtl)',
                'Current Stock Held (Qtl)',
                'Capacity Occupancy (%)',
                'Assigned Supervisor',
                'Contact Phone',
                'Status',
            ]);

            foreach ($warehouses as $wh) {
                $stock = (float) ($stocks[$wh->id] ?? 0);
                $occ = (float) $wh->capacity_quintals > 0 ? min(100, round(($stock / (float) $wh->capacity_quintals) * 100, 1)) : 0;

                fputcsv($handle, [
                    $wh->id,
                    $wh->name,
                    $wh->code,
                    $wh->address ?? '',
                    number_format((float) $wh->capacity_quintals, 2, '.', ''),
                    number_format($stock, 2, '.', ''),
                    $occ . '%',
                    $wh->supervisor?->name ?? 'Unassigned',
                    $wh->phone ?? '',
                    $wh->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('masters.warehouses.form', [
            'warehouse' => new Warehouse(['is_active' => true, 'capacity_quintals' => 5000]),
            'supervisors' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20', 'unique:warehouses,code'],
            'address' => ['nullable', 'string', 'max:255'],
            'capacity_quintals' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = 'WH-' . strtoupper(Str::random(4));
            while (Warehouse::where('code', $data['code'])->exists()) {
                $data['code'] = 'WH-' . strtoupper(Str::random(4));
            }
        } else {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $data['is_active'] = $request->boolean('is_active');
        $warehouse = Warehouse::create($data);

        AuditLogger::log('warehouse.created', $warehouse, [], $warehouse->toArray(), "Created warehouse {$warehouse->name}");

        return redirect()->route('warehouses.index')->with('success', "Warehouse '{$warehouse->name}' created.");
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('masters.warehouses.form', [
            'warehouse' => $warehouse,
            'supervisors' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'capacity_quintals' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');
        $original = $warehouse->getOriginal();
        $warehouse->update($data);

        AuditLogger::changes('warehouse.updated', $warehouse, $original, "Updated warehouse {$warehouse->name}");

        return redirect()->route('warehouses.index')->with('success', "Warehouse '{$warehouse->name}' updated.");
    }

    public function toggle(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update(['is_active' => ! $warehouse->is_active]);
        AuditLogger::log($warehouse->is_active ? 'warehouse.activated' : 'warehouse.deactivated', $warehouse);

        return back()->with('success', "Warehouse '{$warehouse->name}' " . ($warehouse->is_active ? 'activated' : 'deactivated') . '.');
    }
}
