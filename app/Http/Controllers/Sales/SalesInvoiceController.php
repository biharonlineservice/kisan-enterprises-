<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\Customer;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\SalesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesInvoiceController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $query = SalesInvoice::with(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'creator'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->where('invoice_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('invoice_date', '<=', Carbon::parse($request->to)->endOfDay()));

        if ($tab === 'finalized') {
            $query->finalized();
        } elseif ($tab === 'delivered') {
            $query->delivered();
        } elseif ($tab === 'in_transit') {
            $query->inTransit();
        } elseif ($tab === 'unpaid') {
            $query->unpaid();
        } elseif ($tab === 'paid') {
            $query->paid();
        } elseif ($tab === 'draft') {
            $query->draft();
        } elseif ($tab === 'reversed') {
            $query->reversed();
        }

        $invoices = $query->latest('invoice_date')->latest('id')->paginate(20)->withQueryString();

        $totalInvoiced = (string) SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->sum('total_amount');
        $totalPaid = (string) SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->sum('paid_amount');
        $totalDue = (string) SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->sum('balance_due');
        $totalDispatchedQtl = (string) SalesInvoiceItem::whereHas('invoice', fn ($q) => $q->where('status', SalesInvoice::STATUS_FINALIZED))->sum('quantity_qtl');
        $finalizedCount = SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->count();
        $totalInvoicesCount = SalesInvoice::count();
        $unpaidCount = SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->where('balance_due', '>', 0)->count();
        $activeBuyersCount = SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->distinct('customer_id')->count('customer_id');
        $deliveredCount = SalesInvoice::delivered()->count();
        $inTransitCount = SalesInvoice::inTransit()->count();

        $kpis = [
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalDue' => $totalDue,
            'totalDispatchedQtl' => $totalDispatchedQtl,
            'finalizedCount' => $finalizedCount,
            'totalInvoicesCount' => $totalInvoicesCount,
            'unpaidCount' => $unpaidCount,
            'activeBuyersCount' => $activeBuyersCount,
            'deliveredCount' => $deliveredCount,
            'inTransitCount' => $inTransitCount,
        ];

        $tabCounts = [
            'all' => $totalInvoicesCount,
            'finalized' => $finalizedCount,
            'delivered' => $deliveredCount,
            'in_transit' => $inTransitCount,
            'unpaid' => $unpaidCount,
            'paid' => SalesInvoice::where('status', SalesInvoice::STATUS_FINALIZED)->where('balance_due', '<=', 0)->count(),
            'draft' => SalesInvoice::where('status', SalesInvoice::STATUS_DRAFT)->count(),
            'reversed' => SalesInvoice::where('status', SalesInvoice::STATUS_REVERSED)->count(),
        ];

        return view('sales.invoices.index', [
            'invoices' => $invoices,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'kpis' => $kpis,
            'tabCounts' => $tabCounts,
            'activeTab' => $tab,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = SalesInvoice::with(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'creator'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->where('invoice_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('invoice_date', '<=', Carbon::parse($request->to)->endOfDay()));

        $tab = $request->input('tab');
        if ($tab === 'finalized') {
            $query->finalized();
        } elseif ($tab === 'delivered') {
            $query->delivered();
        } elseif ($tab === 'in_transit') {
            $query->inTransit();
        } elseif ($tab === 'unpaid') {
            $query->unpaid();
        } elseif ($tab === 'paid') {
            $query->paid();
        } elseif ($tab === 'draft') {
            $query->draft();
        } elseif ($tab === 'reversed') {
            $query->reversed();
        }

        $filename = 'sales_invoices_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Invoice #',
                'Date',
                'Status',
                'Customer ID',
                'Customer / Mill Name',
                'Customer Mobile',
                'Mandi / City',
                'Commodities & Varieties',
                'Total Dispatched (Qtl)',
                'Subtotal Amount (₹)',
                'Tax Amount (₹)',
                'Discount Amount (₹)',
                'Total Amount (₹)',
                'Paid Amount (₹)',
                'Balance Due (₹)',
                'Vehicle Number',
                'Driver Name',
                'Transporter',
                'E-Way Bill #',
                'Delivery Destination',
                'Delivery Status',
                'Delivered At',
                'Received By',
                'Remarks',
            ]);

            $query->latest('invoice_date')->chunk(200, function ($invoices) use ($handle) {
                foreach ($invoices as $inv) {
                    $cropItems = $inv->items->map(function ($item) {
                        $cropName = $item->crop->name ?? 'Crop';
                        $varName = $item->cropVariety ? " ({$item->cropVariety->name})" : '';
                        return "{$cropName}{$varName}: {$item->quantity_qtl} Qtl";
                    })->implode(' | ');

                    fputcsv($handle, [
                        $inv->invoice_number,
                        $inv->invoice_date?->format('Y-m-d') ?? '',
                        strtoupper($inv->status),
                        $inv->customer?->customer_id ?? '',
                        $inv->customer?->name ?? '',
                        $inv->customer?->phone ?? $inv->customer?->mobile ?? '',
                        $inv->customer?->city ?? '',
                        $cropItems,
                        $inv->totalQuantityQtl(),
                        $inv->subtotal_amount,
                        $inv->tax_amount,
                        $inv->discount_amount,
                        $inv->total_amount,
                        $inv->paid_amount,
                        $inv->balance_due,
                        $inv->vehicle_number ?? '',
                        $inv->driver_name ?? '',
                        $inv->transporter_name ?? '',
                        $inv->eway_bill_number ?? '',
                        $inv->delivery_destination ?? '',
                        strtoupper($inv->delivery_status ?? 'in_transit'),
                        $inv->delivered_at?->format('Y-m-d H:i') ?? '',
                        $inv->received_by ?? '',
                        $inv->remarks ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(Request $request): View
    {
        $selectedOrder = null;
        if ($request->filled('order_id')) {
            $selectedOrder = SalesOrder::with(['customer', 'items'])->find($request->order_id);
        }

        $selectedPurchase = null;
        if ($request->filled('purchase_id')) {
            $selectedPurchase = \App\Models\Purchase::with(['farmer', 'crop.varieties', 'cropVariety', 'warehouse'])->find($request->purchase_id);
        }

        return view('sales.invoices.form', [
            'invoice' => new SalesInvoice([
                'invoice_date' => now(),
                'status' => 'draft',
                'discount_amount' => 0.00,
            ]),
            'selectedOrder' => $selectedOrder,
            'selectedPurchase' => $selectedPurchase,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'crops' => Crop::with('varieties')->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:30'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'transporter_name' => ['nullable', 'string', 'max:100'],
            'eway_bill_number' => ['nullable', 'string', 'max:50'],
            'delivery_destination' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'initial_paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.crop_id' => ['required', 'exists:crops,id'],
            'items.*.crop_variety_id' => ['nullable', 'exists:crop_varieties,id'],
            'items.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'items.*.quantity_qtl' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate_per_qtl' => ['required', 'numeric', 'min:1'],
        ]);

        $finalize = $request->input('action') === 'finalize';

        try {
            $invoice = $this->sales->createInvoice($data, $data['items'], $request->user()->id, $finalize);

            $msg = $finalize
                ? "Sales invoice {$invoice->invoice_number} finalized and stock dispatched from inventory."
                : "Sales invoice draft {$invoice->invoice_number} saved.";

            return redirect()->route('sales-invoices.show', $invoice)->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(SalesInvoice $salesInvoice): View
    {
        $salesInvoice->load([
            'customer',
            'items.crop',
            'items.cropVariety',
            'items.warehouse',
            'creator',
            'updater',
            'reverser',
            'deliverer',
            'receipts.creator',
        ]);

        return view('sales.invoices.show', ['invoice' => $salesInvoice]);
    }

    public function finalize(Request $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $this->sales->finalizeInvoice(
                $salesInvoice,
                $request->user()->id,
                $request->input('payment_amount', '0.00'),
                $request->input('payment_method', 'bank_transfer'),
                $request->input('payment_reference')
            );

            return redirect()->route('sales-invoices.show', $salesInvoice)
                ->with('success', "Sales invoice {$salesInvoice->invoice_number} finalized. Stock dispatched from warehouse.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reverse(Request $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        try {
            $this->sales->reverseInvoice($salesInvoice, $request->reason, $request->user()->id);

            return redirect()->route('sales-invoices.show', $salesInvoice)
                ->with('success', "Sales invoice {$salesInvoice->invoice_number} cancelled/reversed. Stock restored to inventory.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markDelivered(Request $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        $validated = $request->validate([
            'delivered_at' => ['nullable', 'date'],
            'received_by' => ['nullable', 'string', 'max:100'],
            'delivery_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->sales->markDelivered($salesInvoice, $validated, $request->user()->id);

            return redirect()->route('sales-invoices.show', $salesInvoice)
                ->with('success', "Invoice {$salesInvoice->invoice_number} successfully marked as delivered to buyer.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revertDelivery(Request $request, SalesInvoice $salesInvoice): RedirectResponse
    {
        try {
            $this->sales->revertDelivery($salesInvoice, $request->user()->id);

            return redirect()->route('sales-invoices.show', $salesInvoice)
                ->with('success', "Delivery status for invoice {$salesInvoice->invoice_number} reverted to in-transit.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function print(SalesInvoice $salesInvoice): View
    {
        $salesInvoice->load(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'receipts', 'creator']);

        return view('sales.invoices.print', ['invoice' => $salesInvoice]);
    }

    public function pdf(SalesInvoice $salesInvoice): Response
    {
        $salesInvoice->load(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'receipts', 'creator']);

        $pdf = Pdf::loadView('sales.invoices.print', ['invoice' => $salesInvoice, 'isPdf' => true])
            ->setPaper('a4', 'portrait');

        $filename = 'Sales_Invoice_' . str_replace(['/', '\\'], '_', $salesInvoice->invoice_number) . '.pdf';

        return $pdf->download($filename);
    }

    public function challan(SalesInvoice $salesInvoice): View
    {
        $salesInvoice->load(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'creator']);

        return view('sales.invoices.challan', ['invoice' => $salesInvoice]);
    }

    public function challanPdf(SalesInvoice $salesInvoice): Response
    {
        $salesInvoice->load(['customer', 'items.crop', 'items.cropVariety', 'items.warehouse', 'creator']);

        $pdf = Pdf::loadView('sales.invoices.challan', ['invoice' => $salesInvoice, 'isPdf' => true])
            ->setPaper('a4', 'portrait');

        $challanNumber = $salesInvoice->challanNumber();
        $filename = 'Transport_Challan_' . str_replace(['/', '\\'], '_', $challanNumber) . '.pdf';

        return $pdf->download($filename);
    }
}
