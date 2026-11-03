<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use App\Services\DocumentNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'all');

        $query = SalesOrder::with(['customer', 'items.crop', 'items.warehouse', 'creator'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->where('order_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('order_date', '<=', Carbon::parse($request->to)->endOfDay()));

        if ($tab === 'confirmed') {
            $query->where('status', SalesOrder::STATUS_CONFIRMED);
        } elseif ($tab === 'dispatched') {
            $query->where('status', SalesOrder::STATUS_DISPATCHED);
        } elseif ($tab === 'completed') {
            $query->where('status', SalesOrder::STATUS_COMPLETED);
        } elseif ($tab === 'cancelled') {
            $query->where('status', SalesOrder::STATUS_CANCELLED);
        }

        $orders = $query->latest('order_date')->paginate(15)->withQueryString();

        // High-level KPI aggregations
        $totalOrders = SalesOrder::count();
        $confirmedOrders = SalesOrder::where('status', SalesOrder::STATUS_CONFIRMED)->count();
        $dispatchedOrders = SalesOrder::where('status', SalesOrder::STATUS_DISPATCHED)->count();
        $completedOrders = SalesOrder::where('status', SalesOrder::STATUS_COMPLETED)->count();
        $cancelledOrders = SalesOrder::where('status', SalesOrder::STATUS_CANCELLED)->count();

        $totalQtlBooked = (float) SalesOrder::whereIn('status', [
            SalesOrder::STATUS_CONFIRMED,
            SalesOrder::STATUS_DISPATCHED,
            SalesOrder::STATUS_COMPLETED,
        ])->sum('total_qtl');

        $totalOrderValue = (float) SalesOrder::whereIn('status', [
            SalesOrder::STATUS_CONFIRMED,
            SalesOrder::STATUS_DISPATCHED,
            SalesOrder::STATUS_COMPLETED,
        ])->sum('total_amount');

        $pendingFulfillmentQtl = (float) SalesOrder::where('status', SalesOrder::STATUS_CONFIRMED)->sum('total_qtl');

        $activeBuyersCount = SalesOrder::whereIn('status', [
            SalesOrder::STATUS_CONFIRMED,
            SalesOrder::STATUS_DISPATCHED,
        ])->distinct('customer_id')->count('customer_id');

        $riceMillsCount = SalesOrder::whereHas('customer', fn ($c) => $c->where('customer_type', 'rice_mill'))
            ->whereIn('status', [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_DISPATCHED])
            ->distinct('customer_id')
            ->count('customer_id');

        $tradersCount = max(0, $activeBuyersCount - $riceMillsCount);

        $kpis = [
            'totalOrders' => $totalOrders,
            'confirmedOrders' => $confirmedOrders,
            'dispatchedOrders' => $dispatchedOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'totalQtlBooked' => $totalQtlBooked,
            'totalOrderValue' => $totalOrderValue,
            'pendingFulfillmentQtl' => $pendingFulfillmentQtl,
            'activeBuyersCount' => $activeBuyersCount,
            'riceMillsCount' => $riceMillsCount,
            'tradersCount' => $tradersCount,
        ];

        $tabCounts = [
            'all' => $totalOrders,
            'confirmed' => $confirmedOrders,
            'dispatched' => $dispatchedOrders,
            'completed' => $completedOrders,
            'cancelled' => $cancelledOrders,
        ];

        return view('sales.orders.index', [
            'orders' => $orders,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'kpis' => $kpis,
            'tabCounts' => $tabCounts,
            'activeTab' => $tab,
        ]);
    }

    public function create(): View
    {
        return view('sales.orders.form', [
            'order' => new SalesOrder([
                'order_date' => now(),
                'status' => 'draft',
            ]),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::with('varieties')->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.crop_id' => ['required', 'exists:crops,id'],
            'items.*.crop_variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'items.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'items.*.quantity_qtl' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate_per_qtl' => ['required', 'numeric', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $orderNumber = DocumentNumber::next('sales_order');

            $totalQtl = '0.000';
            $totalAmount = '0.00';

            foreach ($data['items'] as $item) {
                $qty = bc_round((string) $item['quantity_qtl'], 3);
                $rate = bc_round((string) $item['rate_per_qtl'], 2);
                $amt = bc_round(bcmul($qty, $rate, 4), 2);

                $totalQtl = bcadd($totalQtl, $qty, 3);
                $totalAmount = bcadd($totalAmount, $amt, 2);
            }

            $order = SalesOrder::create([
                'order_number' => $orderNumber,
                'order_date' => $data['order_date'],
                'customer_id' => $data['customer_id'],
                'status' => SalesOrder::STATUS_CONFIRMED,
                'total_qtl' => $totalQtl,
                'total_amount' => $totalAmount,
                'delivery_address' => $data['delivery_address'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $qty = bc_round((string) $item['quantity_qtl'], 3);
                $rate = bc_round((string) $item['rate_per_qtl'], 2);
                $amt = bc_round(bcmul($qty, $rate, 4), 2);

                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'crop_id' => $item['crop_id'],
                    'crop_variety_id' => $item['crop_variety_id'] ?? null,
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity_qtl' => $qty,
                    'rate_per_qtl' => $rate,
                    'amount' => $amt,
                ]);
            }

            return $order;
        });

        return redirect()->route('sales-orders.show', $order)
            ->with('success', "Sales order {$order->order_number} confirmed.");
    }

    public function show(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'creator', 'invoices']);

        $totalQtl = (float) $salesOrder->total_qtl;
        $dispatchedQtl = (float) $salesOrder->dispatched_qtl;
        $remainingQtl = max(0, $totalQtl - $dispatchedQtl);
        $progressPercent = $totalQtl > 0 ? min(100, round(($dispatchedQtl / $totalQtl) * 100, 1)) : 0;
        $invoicedAmount = (float) $salesOrder->dispatched_amount;

        return view('sales.orders.show', [
            'order' => $salesOrder,
            'totalQtl' => $totalQtl,
            'dispatchedQtl' => $dispatchedQtl,
            'remainingQtl' => $remainingQtl,
            'progressPercent' => $progressPercent,
            'invoicedAmount' => $invoicedAmount,
        ]);
    }

    public function print(SalesOrder $salesOrder): View
    {
        $salesOrder->load(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'creator']);

        return view('sales.orders.print', ['order' => $salesOrder]);
    }

    public function export(Request $request): StreamedResponse
    {
        AuditLogger::log('sales_order.exported', null, [], [], 'Exported sales orders CSV');

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="sales_orders_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order Number', 'Order Date', 'Customer ID', 'Customer / Mill Name',
                'Category', 'City', 'Commodities Summary', 'Booked Quantity (Qtl)',
                'Contract Value (₹)', 'Status', 'Created By',
            ]);

            $tab = $request->query('tab', 'all');

            $query = SalesOrder::with(['customer', 'items.crop', 'creator'])
                ->when($request->filled('q'), fn ($q) => $q->search($request->q))
                ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('from'), fn ($q) => $q->where('order_date', '>=', Carbon::parse($request->from)->startOfDay()))
                ->when($request->filled('to'), fn ($q) => $q->where('order_date', '<=', Carbon::parse($request->to)->endOfDay()));

            if ($tab === 'confirmed') {
                $query->where('status', SalesOrder::STATUS_CONFIRMED);
            } elseif ($tab === 'dispatched') {
                $query->where('status', SalesOrder::STATUS_DISPATCHED);
            } elseif ($tab === 'completed') {
                $query->where('status', SalesOrder::STATUS_COMPLETED);
            } elseif ($tab === 'cancelled') {
                $query->where('status', SalesOrder::STATUS_CANCELLED);
            }

            $query->latest('order_date')->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $o) {
                    $crops = $o->items->map(fn ($i) => ($i->crop?->name ?? 'Crop') . ' (' . bc_round($i->quantity_qtl, 3) . ' Qtl)')->implode(', ');
                    fputcsv($handle, [
                        $o->order_number,
                        fmt_date($o->order_date),
                        $o->customer?->customer_id ?? '',
                        $o->customer?->name ?? '',
                        $o->customer?->type_name ?? '',
                        $o->customer?->city ?? '',
                        $crops,
                        bc_round($o->total_qtl, 3),
                        bc_round($o->total_amount, 2),
                        ucfirst($o->status),
                        $o->creator?->name ?? 'System',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function markCompleted(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        try {
            $salesOrder->update([
                'status' => SalesOrder::STATUS_COMPLETED,
                'updated_by' => $request->user()->id,
            ]);

            AuditLogger::log('sales_order.completed', $salesOrder, [], ['status' => 'completed'], "Marked sales order {$salesOrder->order_number} as completed / buyer delivered", $request->user()->id);

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', "Sales Order {$salesOrder->order_number} marked as completed & delivered.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
