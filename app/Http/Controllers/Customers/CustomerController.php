<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Services\AuditLogger;
use App\Services\DocumentNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'all');

        $query = Customer::query()
            ->search($request->q)
            ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city))
            ->when($request->filled('type'), fn ($q) => $q->where('customer_type', $request->type))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->when($request->boolean('has_due'), function ($q) {
                $q->where(function ($sub) {
                    $sub->where('opening_balance', '>', 0)
                        ->orWhereHas('salesInvoices', fn ($inv) => $inv->where('status', 'finalized')->where('balance_due', '>', 0));
                });
            });

        // Tab routing
        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'rice_mills') {
            $query->where('customer_type', 'rice_mill');
        } elseif ($tab === 'wholesalers') {
            $query->where('customer_type', 'wholesaler');
        } elseif ($tab === 'traders') {
            $query->where('customer_type', 'trader');
        } elseif ($tab === 'has_due') {
            $query->where(function ($sub) {
                $sub->where('opening_balance', '>', 0)
                    ->orWhereHas('salesInvoices', fn ($inv) => $inv->where('status', 'finalized')->where('balance_due', '>', 0));
            });
        }

        // Subquery aggregation for live ledger calculation without N+1
        $query->withSum(['salesInvoices as invoices_balance_due' => fn ($q) => $q->where('status', 'finalized')], 'balance_due')
              ->withSum(['salesInvoices as total_invoiced_amount' => fn ($q) => $q->where('status', 'finalized')], 'total_amount')
              ->withCount(['salesInvoices as unpaid_invoices_count' => fn ($q) => $q->where('status', 'finalized')->where('balance_due', '>', 0)]);

        // Sorting
        $sort = $request->query('sort', 'name_asc');
        match ($sort) {
            'name_desc' => $query->orderByDesc('name'),
            'id_asc' => $query->orderBy('customer_id'),
            'id_desc' => $query->orderByDesc('customer_id'),
            'limit_desc' => $query->orderByDesc('credit_limit'),
            'balance_desc' => $query->orderByDesc(
                DB::raw('COALESCE(opening_balance, 0) + (SELECT COALESCE(SUM(balance_due), 0) FROM sales_invoices WHERE sales_invoices.customer_id = customers.id AND sales_invoices.status = "finalized")')
            ),
            default => $query->orderBy('name'),
        };

        $customers = $query->paginate(15)->withQueryString();

        // Network Metrics & High-level KPIs
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $inactiveCustomers = $totalCustomers - $activeCustomers;
        $riceMillsCount = Customer::where('customer_type', 'rice_mill')->count();
        $wholesalersCount = Customer::where('customer_type', 'wholesaler')->count();
        $tradersCount = Customer::where('customer_type', 'trader')->count();
        $citiesCount = Customer::whereNotNull('city')->where('city', '!=', '')->distinct()->count('city');
        $totalCreditLimit = (float) Customer::where('is_active', true)->sum('credit_limit');

        $totalReceivables = (float) Customer::sum('opening_balance') + (float) SalesInvoice::where('status', 'finalized')->sum('balance_due');
        $hasDueCount = Customer::where(function ($sub) {
            $sub->where('opening_balance', '>', 0)
                ->orWhereHas('salesInvoices', fn ($inv) => $inv->where('status', 'finalized')->where('balance_due', '>', 0));
        })->count();

        $kpis = [
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'inactiveCustomers' => $inactiveCustomers,
            'riceMillsCount' => $riceMillsCount,
            'wholesalersCount' => $wholesalersCount,
            'tradersCount' => $tradersCount,
            'citiesCount' => $citiesCount,
            'totalCreditLimit' => $totalCreditLimit,
            'totalReceivables' => $totalReceivables,
            'hasDueCount' => $hasDueCount,
        ];

        $tabCounts = [
            'all' => $totalCustomers,
            'active' => $activeCustomers,
            'rice_mills' => $riceMillsCount,
            'wholesalers' => $wholesalersCount,
            'traders' => $tradersCount,
            'has_due' => $hasDueCount,
            'inactive' => $inactiveCustomers,
        ];

        $cities = Customer::whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('customers.index', [
            'customers' => $customers,
            'types' => Customer::TYPES,
            'kpis' => $kpis,
            'tabCounts' => $tabCounts,
            'cities' => $cities,
            'activeTab' => $tab,
        ]);
    }

    public function create(): View
    {
        return view('customers.form', [
            'customer' => new Customer([
                'is_active' => true,
                'customer_type' => 'rice_mill',
                'credit_limit' => 0.00,
                'payment_terms_days' => 15,
                'opening_balance' => 0.00,
                'state' => setting('business.state', ''),
            ]),
            'types' => Customer::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $customer = DB::transaction(function () use ($data, $request) {
            $customerId = DocumentNumber::next('customer');

            $customer = Customer::create([
                ...$data,
                'customer_id' => $customerId,
                'is_active' => $request->boolean('is_active'),
                'created_by' => $request->user()->id,
            ]);

            AuditLogger::log('customer.created', $customer, [], $customer->toArray(), "Created customer {$customer->name} ({$customer->customer_id})");

            return $customer;
        });

        return redirect()->route('customers.show', $customer)->with('success', "Customer '{$customer->name}' added with ID {$customer->customer_id}.");
    }

    public function show(Customer $customer): View
    {
        $customer->load('creator');

        $salesOrders = $customer->salesOrders()
            ->latest('order_date')
            ->take(10)
            ->get();

        $salesInvoices = $customer->salesInvoices()
            ->latest('invoice_date')
            ->take(10)
            ->get();

        $receipts = $customer->receipts()
            ->latest('receipt_date')
            ->take(10)
            ->get();

        $openingBalance = (float) ($customer->opening_balance ?? 0);
        $invoicesDue = (float) $customer->salesInvoices()->where('status', 'finalized')->sum('balance_due');
        $netOutstanding = $openingBalance + $invoicesDue;
        $totalInvoiced = (float) $customer->salesInvoices()->where('status', 'finalized')->sum('total_amount');
        $totalCollected = (float) $customer->receipts()->whereNull('reversed_at')->sum('amount');
        $creditLimit = (float) ($customer->credit_limit ?? 0);
        $availableCredit = max(0, $creditLimit - $netOutstanding);
        $creditUtilization = $creditLimit > 0 ? min(100, round(($netOutstanding / $creditLimit) * 100, 1)) : 0;

        return view('customers.show', [
            'customer' => $customer,
            'salesOrders' => $salesOrders,
            'salesInvoices' => $salesInvoices,
            'receipts' => $receipts,
            'openingBalance' => $openingBalance,
            'invoicesDue' => $invoicesDue,
            'netOutstanding' => $netOutstanding,
            'totalInvoiced' => $totalInvoiced,
            'totalCollected' => $totalCollected,
            'creditLimit' => $creditLimit,
            'availableCredit' => $availableCredit,
            'creditUtilization' => $creditUtilization,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', [
            'customer' => $customer,
            'types' => Customer::TYPES,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer);

        DB::transaction(function () use ($data, $customer, $request) {
            $original = $customer->getOriginal();

            $customer->update([
                ...$data,
                'is_active' => $request->boolean('is_active'),
            ]);

            AuditLogger::changes('customer.updated', $customer, $original, "Updated customer {$customer->name}");
        });

        return redirect()->route('customers.show', $customer)->with('success', "Customer '{$customer->name}' updated.");
    }

    public function toggle(Customer $customer): RedirectResponse
    {
        $customer->update(['is_active' => ! $customer->is_active]);
        AuditLogger::log($customer->is_active ? 'customer.activated' : 'customer.deactivated', $customer);

        return back()->with('success', "Customer '{$customer->name}' " . ($customer->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function print(Customer $customer): View
    {
        return view('customers.print', compact('customer'));
    }

    public function export(Request $request): StreamedResponse
    {
        AuditLogger::log('customer.exported', null, [], [], 'Exported customers CSV');

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="customers_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Customer ID', 'Business Name', 'Category', 'Contact Person', 'Mobile',
                'Email', 'City', 'State', 'GSTIN', 'PAN', 'Credit Limit', 'Payment Terms (Days)',
                'Opening Balance', 'Net Receivables Due', 'Status',
            ]);

            $tab = $request->query('tab', 'all');

            $query = Customer::query()
                ->search($request->q)
                ->when($request->filled('city'), fn ($q) => $q->where('city', $request->city))
                ->when($request->filled('type'), fn ($q) => $q->where('customer_type', $request->type))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->when($request->boolean('has_due'), function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('opening_balance', '>', 0)
                            ->orWhereHas('salesInvoices', fn ($inv) => $inv->where('status', 'finalized')->where('balance_due', '>', 0));
                    });
                });

            if ($tab === 'active') {
                $query->where('is_active', true);
            } elseif ($tab === 'inactive') {
                $query->where('is_active', false);
            } elseif ($tab === 'rice_mills') {
                $query->where('customer_type', 'rice_mill');
            } elseif ($tab === 'wholesalers') {
                $query->where('customer_type', 'wholesaler');
            } elseif ($tab === 'traders') {
                $query->where('customer_type', 'trader');
            } elseif ($tab === 'has_due') {
                $query->where(function ($sub) {
                    $sub->where('opening_balance', '>', 0)
                        ->orWhereHas('salesInvoices', fn ($inv) => $inv->where('status', 'finalized')->where('balance_due', '>', 0));
                });
            }

            $query->withSum(['salesInvoices as invoices_balance_due' => fn ($q) => $q->where('status', 'finalized')], 'balance_due')
                ->orderBy('customer_id')
                ->chunk(200, function ($customers) use ($handle) {
                    foreach ($customers as $c) {
                        $netDue = (float) $c->opening_balance + (float) ($c->invoices_balance_due ?? 0);
                        fputcsv($handle, [
                            $c->customer_id,
                            $c->name,
                            $c->type_name,
                            $c->contact_person ?: '',
                            $c->mobile ?: '',
                            $c->email ?: '',
                            $c->city ?: '',
                            $c->state ?: '',
                            $c->gstin ?: '',
                            $c->pan ?: '',
                            $c->credit_limit,
                            $c->payment_terms_days,
                            $c->opening_balance,
                            number_format($netDue, 2, '.', ''),
                            $c->is_active ? 'Active' : 'Inactive',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'customer_type' => ['required', Rule::in(array_keys(Customer::TYPES))],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'mobile' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'pan' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'opening_balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'gstin.regex' => 'Enter a valid 15-character GSTIN (capital letters).',
            'pan.regex' => 'Enter a valid 10-character PAN (capital letters).',
        ]);
    }
}
