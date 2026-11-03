<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\SalesInvoice;
use App\Services\AuditLogger;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerReceiptController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $query = CustomerReceipt::with(['customer', 'invoice', 'creator', 'reverser'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('from'), fn ($q) => $q->where('receipt_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('receipt_date', '<=', Carbon::parse($request->to)->endOfDay()));

        if ($tab === 'active') {
            $query->active();
        } elseif ($tab === 'bank') {
            $query->active()->whereIn('payment_method', ['bank_transfer', 'neft_rtgs', 'cheque']);
        } elseif ($tab === 'upi') {
            $query->active()->where('payment_method', 'upi');
        } elseif ($tab === 'cash') {
            $query->active()->where('payment_method', 'cash');
        } elseif ($tab === 'reversed') {
            $query->reversed();
        }

        $receipts = $query->latest('receipt_date')->latest('id')->paginate(20)->withQueryString();

        $activeReceipts = CustomerReceipt::whereNull('reversed_at');
        $totalReceived = (string) (clone $activeReceipts)->sum('amount');
        $activeCount = (clone $activeReceipts)->count();
        $totalCount = CustomerReceipt::count();
        $todayReceived = (string) (clone $activeReceipts)->whereDate('receipt_date', now()->toDateString())->sum('amount');
        $todayCount = (clone $activeReceipts)->whereDate('receipt_date', now()->toDateString())->count();
        $bankReceived = (string) (clone $activeReceipts)->whereIn('payment_method', ['bank_transfer', 'neft_rtgs', 'cheque'])->sum('amount');
        $bankCount = (clone $activeReceipts)->whereIn('payment_method', ['bank_transfer', 'neft_rtgs', 'cheque'])->count();
        $upiReceived = (string) (clone $activeReceipts)->where('payment_method', 'upi')->sum('amount');
        $upiCount = (clone $activeReceipts)->where('payment_method', 'upi')->count();
        $cashReceived = (string) (clone $activeReceipts)->where('payment_method', 'cash')->sum('amount');
        $cashCount = (clone $activeReceipts)->where('payment_method', 'cash')->count();
        $reversedCount = CustomerReceipt::whereNotNull('reversed_at')->count();

        $kpis = [
            'totalReceived' => $totalReceived,
            'activeCount' => $activeCount,
            'totalCount' => $totalCount,
            'todayReceived' => $todayReceived,
            'todayCount' => $todayCount,
            'bankReceived' => $bankReceived,
            'bankCount' => $bankCount,
            'upiReceived' => $upiReceived,
            'upiCount' => $upiCount,
            'cashReceived' => $cashReceived,
            'cashCount' => $cashCount,
            'reversedCount' => $reversedCount,
        ];

        $tabCounts = [
            'all' => $totalCount,
            'active' => $activeCount,
            'bank' => $bankCount,
            'upi' => $upiCount,
            'cash' => $cashCount,
            'reversed' => $reversedCount,
        ];

        return view('sales.receipts.index', [
            'receipts' => $receipts,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'methods' => CustomerReceipt::METHODS,
            'kpis' => $kpis,
            'tabCounts' => $tabCounts,
            'activeTab' => $tab,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = CustomerReceipt::with(['customer', 'invoice', 'creator', 'reverser'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('from'), fn ($q) => $q->where('receipt_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('receipt_date', '<=', Carbon::parse($request->to)->endOfDay()));

        $tab = $request->input('tab');
        if ($tab === 'active') {
            $query->active();
        } elseif ($tab === 'bank') {
            $query->active()->whereIn('payment_method', ['bank_transfer', 'neft_rtgs', 'cheque']);
        } elseif ($tab === 'upi') {
            $query->active()->where('payment_method', 'upi');
        } elseif ($tab === 'cash') {
            $query->active()->where('payment_method', 'cash');
        } elseif ($tab === 'reversed') {
            $query->reversed();
        }

        $filename = 'customer_receipts_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Receipt Voucher #',
                'Receipt Date',
                'Status',
                'Customer ID',
                'Customer / Mill Name',
                'Customer Mobile',
                'City / Mandi',
                'Linked Sales Invoice #',
                'Payment Mode',
                'Transaction Reference / UTR',
                'Remitting Bank',
                'Received Amount (₹)',
                'Remarks & Notes',
                'Recorded By',
                'Recorded At',
            ]);

            $query->latest('receipt_date')->chunk(200, function ($receipts) use ($handle) {
                foreach ($receipts as $r) {
                    fputcsv($handle, [
                        $r->receipt_number,
                        $r->receipt_date?->format('Y-m-d') ?? '',
                        $r->isReversed() ? 'REVERSED' : 'COMPLETED',
                        $r->customer?->customer_id ?? '',
                        $r->customer?->name ?? '',
                        $r->customer?->phone ?? $r->customer?->mobile ?? '',
                        $r->customer?->city ?? '',
                        $r->invoice?->invoice_number ?? '',
                        $r->method_label,
                        $r->transaction_reference ?? '',
                        $r->bank_name ?? '',
                        $r->amount,
                        $r->notes ?? '',
                        $r->creator?->name ?? '',
                        $r->created_at?->format('Y-m-d H:i:s') ?? '',
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
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = SalesInvoice::with('customer')->find($request->invoice_id);
        }

        $pendingInvoices = SalesInvoice::with('customer')
            ->where('status', SalesInvoice::STATUS_FINALIZED)
            ->where('balance_due', '>', 0)
            ->orderBy('invoice_date')
            ->get();

        return view('sales.receipts.form', [
            'receipt' => new CustomerReceipt([
                'receipt_date' => now(),
                'payment_method' => 'bank_transfer',
                'amount' => $selectedInvoice ? $selectedInvoice->balance_due : 0.00,
            ]),
            'selectedInvoice' => $selectedInvoice,
            'pendingInvoices' => $pendingInvoices,
            'methods' => CustomerReceipt::METHODS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'payment_method' => ['required', 'in:cash,bank_transfer,neft_rtgs,upi,cheque'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $invoice = SalesInvoice::findOrFail($data['sales_invoice_id']);
        if (! $invoice->isFinalized()) {
            return back()->withInput()->with('error', 'Receipts can only be recorded against finalized sales invoices.');
        }

        $receipt = $this->sales->recordReceipt(
            $invoice,
            $data['amount'],
            $data['payment_method'],
            $data['transaction_reference'] ?? null,
            $data['bank_name'] ?? null,
            $data['notes'] ?? null,
            $request->user()->id
        );

        return redirect()->route('customer-receipts.show', $receipt)
            ->with('success', "Receipt voucher {$receipt->receipt_number} recorded. Remaining invoice balance: " . money($invoice->fresh()->balance_due));
    }

    public function show(CustomerReceipt $customerReceipt): View
    {
        $customerReceipt->load(['customer', 'invoice.items.crop', 'creator', 'reverser']);

        return view('sales.receipts.show', ['receipt' => $customerReceipt]);
    }

    public function print(CustomerReceipt $customerReceipt): View
    {
        $customerReceipt->load(['customer', 'invoice', 'creator']);

        return view('sales.receipts.print', ['receipt' => $customerReceipt]);
    }

    public function reverse(Request $request, CustomerReceipt $customerReceipt): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        if ($customerReceipt->isReversed()) {
            return back()->with('error', 'This customer receipt voucher is already reversed.');
        }

        DB::transaction(function () use ($customerReceipt, $request) {
            $invoice = $customerReceipt->invoice;

            $newPaid = bcsub((string) $invoice->paid_amount, (string) $customerReceipt->amount, 2);
            $newBalance = bcadd((string) $invoice->balance_due, (string) $customerReceipt->amount, 2);

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
            ]);

            $customerReceipt->update([
                'reversed_at' => now(),
                'reversed_by' => $request->user()->id,
                'reversal_reason' => $request->reason,
            ]);

            AuditLogger::log('customer_receipt.reversed', $customerReceipt, [], ['reason' => $request->reason], "Reversed customer receipt {$customerReceipt->receipt_number}", $request->user()->id);
        });

        return redirect()->route('customer-receipts.show', $customerReceipt)->with('success', "Customer receipt voucher {$customerReceipt->receipt_number} reversed.");
    }
}
