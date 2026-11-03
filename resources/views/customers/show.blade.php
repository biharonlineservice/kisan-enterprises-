@extends('layouts.app')

@section('title', $customer->name . ' (' . $customer->customer_id . ')')

@section('content')
{{-- 1. Executive Profile Header & Action Bar --}}
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div class="flex-grow-1" style="min-width: 0;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-success text-decoration-none">{{ __('sales.customers.title') }}</a></li>
                <li class="breadcrumb-item active text-truncate">{{ $customer->name }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h2 class="h4 fw-bold mb-0 text-dark">{{ $customer->name }}</h2>
            <code class="badge bg-light text-primary border fs-6 px-2 py-1">{{ $customer->customer_id }}</code>
            <span class="badge bg-light text-dark border px-2 py-1">{{ $customer->type_name }}</span>
            @if($customer->is_active)
                <span class="badge-status badge-success">{{ __('app.active') }}</span>
            @else
                <span class="badge-status badge-danger">{{ __('app.inactive') }}</span>
            @endif
        </div>
        <div class="small text-muted mt-1">
            <i class="bi bi-geo-alt me-1 text-secondary"></i>{{ $customer->city ? $customer->city . ($customer->state ? ', ' . $customer->state : '') : 'Mandi not set' }}
            &bull; <i class="bi bi-person me-1 text-secondary"></i>{{ $customer->contact_person ?: 'Direct Account' }}
            @if($customer->mobile)
                &bull; <i class="bi bi-telephone me-1 text-secondary"></i>{{ $customer->formatted_mobile ?? $customer->mobile }}
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('sales.customers.tab_all') }}
        </a>
        <a href="{{ route('customers.print', $customer) }}" class="btn btn-sm btn-light border" target="_blank">
            <i class="bi bi-printer me-1"></i> {{ __('sales.customers.print_profile') }}
        </a>
        @can('sales.create')
            @if(\Illuminate\Support\Facades\Route::has('sales-invoices.create'))
                <a href="{{ route('sales-invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-receipt me-1"></i> {{ __('sales.customers.create_invoice') }}
                </a>
            @endif
        @endcan
        @can('accounts.manage')
            @if(\Illuminate\Support\Facades\Route::has('customer-receipts.create'))
                <a href="{{ route('customer-receipts.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-cash-stack me-1"></i> {{ __('sales.customers.record_receipt') }}
                </a>
            @endif
        @endcan
        @can('customers.manage')
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-light border">
                <i class="bi bi-pencil me-1"></i> {{ __('sales.customers.edit_details') }}
            </a>
            <form method="POST" action="{{ route('customers.toggle', $customer) }}" class="d-inline" onsubmit="return confirm('Change status for {{ $customer->name }}?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-light text-{{ $customer->is_active ? 'danger' : 'success' }} border">
                    <i class="bi bi-{{ $customer->is_active ? 'pause-circle' : 'play-circle' }} me-1"></i>
                    {{ $customer->is_active ? __('app.inactive') : __('app.active') }}
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Firm Particulars & Identifiers --}}
    <div class="col-lg-4">
        {{-- Profile Card --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-body text-center p-4">
                <div class="avatar bg-success-subtle text-success-emphasis border border-success-subtle mx-auto mb-3" style="width: 72px; height: 72px; font-size: 1.8rem; font-weight: 700;">
                    @if($customer->customer_type === 'rice_mill')
                        <i class="bi bi-buildings"></i>
                    @elseif($customer->customer_type === 'wholesaler')
                        <i class="bi bi-boxes"></i>
                    @else
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    @endif
                </div>
                <h5 class="fw-bold mb-1 text-dark">{{ $customer->name }}</h5>
                <span class="badge bg-light text-dark border mb-3">{{ $customer->type_name }}</span>

                <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                    @if($customer->mobile)
                        <a href="tel:{{ $customer->mobile }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="bi bi-telephone-fill me-1"></i> {{ $customer->formatted_mobile ?? $customer->mobile }}
                        </a>
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $customer->mobile) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3" title="WhatsApp">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                    @else
                        <span class="text-muted small">No mobile number provided</span>
                    @endif
                </div>
            </div>

            <div class="card-footer bg-light p-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">{{ __('sales.customers.opening_balance') }}</span>
                    <span class="fw-semibold {{ $openingBalance > 0 ? 'text-danger' : ($openingBalance < 0 ? 'text-success' : 'text-muted') }}">
                        {{ money($openingBalance) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">{{ __('sales.customers.credit_limit') }}</span>
                    <span class="fw-semibold text-dark">{{ money($creditLimit) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">{{ __('sales.customers.payment_terms') }}</span>
                    <span class="small fw-semibold text-dark">
                        @if($customer->payment_terms_days > 0)
                            {{ $customer->payment_terms_days }} Days
                        @else
                            Cash &amp; Carry
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Tax & Legal Identifiers --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-1"></i> {{ __('sales.customers.card_business_profile') }}</span>
            </div>
            <div class="card-body p-3">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted mb-2">GSTIN</dt>
                    <dd class="col-7 font-monospace fw-bold mb-2">
                        @if($customer->gstin)
                            <span class="badge bg-light text-dark border">{{ $customer->gstin }}</span>
                        @else
                            <span class="text-muted">Unregistered</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted mb-2">PAN Number</dt>
                    <dd class="col-7 font-monospace mb-2">{{ $customer->pan ?: '—' }}</dd>

                    <dt class="col-5 text-muted mb-2">Contact Person</dt>
                    <dd class="col-7 fw-semibold text-dark mb-2">{{ $customer->contact_person ?: '—' }}</dd>

                    <dt class="col-5 text-muted mb-2">Office Phone</dt>
                    <dd class="col-7 mb-2">{{ $customer->alt_phone ?: '—' }}</dd>

                    <dt class="col-5 text-muted mb-2">Email</dt>
                    <dd class="col-7 text-truncate mb-2">{{ $customer->email ?: '—' }}</dd>

                    <dt class="col-5 text-muted mb-2">Account Created</dt>
                    <dd class="col-7 text-muted mb-2">{{ fmt_date($customer->created_at) }}</dd>

                    <dt class="col-5 text-muted">Created By</dt>
                    <dd class="col-7 text-muted">{{ $customer->creator?->name ?: 'System' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Location & Premises Address --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-geo-alt text-success me-1"></i> Delivery & Premises Location</span>
            </div>
            <div class="card-body p-3 small">
                <div class="mb-2">
                    <span class="text-muted d-block">City / Mandi &bull; State</span>
                    <span class="fw-bold text-dark">{{ $customer->city ?: '—' }}{{ $customer->state ? ', ' . $customer->state : '' }}</span>
                </div>
                <div class="pt-2 border-top">
                    <span class="text-muted d-block">Premises / Mill Street Address</span>
                    <span class="text-dark">{{ $customer->address ?: 'Address details not recorded' }}</span>
                </div>
                @if($customer->notes)
                    <div class="pt-2 mt-2 border-top">
                        <span class="text-muted d-block">Internal Business Notes</span>
                        <span class="text-secondary">{{ $customer->notes }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Column: Financial Exposure & Transaction History --}}
    <div class="col-lg-8">
        {{-- Financial Terms & Exposure Card --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark"><i class="bi bi-wallet2 text-success me-1"></i> {{ __('sales.customers.card_credit_exposure') }}</span>
                <span class="badge bg-light text-muted border">Live Ledger</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">{{ __('sales.customers.net_balance') }}</small>
                            <div class="h5 fw-bold mb-0 {{ $netOutstanding > 0 ? 'text-danger' : ($netOutstanding < 0 ? 'text-success' : 'text-dark') }}">
                                {{ money($netOutstanding) }}
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Current Due</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">{{ __('sales.customers.credit_limit') }}</small>
                            <div class="h5 fw-bold mb-0 text-dark">{{ money($creditLimit) }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Approved Limit</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">Available Credit</small>
                            <div class="h5 fw-bold mb-0 text-success">{{ money($availableCredit) }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Headroom</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <small class="text-muted d-block mb-1">{{ __('sales.customers.total_collected') }}</small>
                            <div class="h5 fw-bold mb-0 text-primary">{{ money($totalCollected) }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Lifetime Receipts</small>
                        </div>
                    </div>
                </div>

                {{-- Credit Utilization Progress Bar --}}
                @if($creditLimit > 0)
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                            <span class="text-muted">Credit Limit Utilization</span>
                            <span class="fw-bold {{ $creditUtilization > 90 ? 'text-danger' : 'text-dark' }}">{{ $creditUtilization }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $creditUtilization > 90 ? 'bg-danger' : ($creditUtilization > 65 ? 'bg-warning' : 'bg-success') }}"
                                 role="progressbar"
                                 style="width: {{ min(100, $creditUtilization) }}%;"
                                 aria-valuenow="{{ $creditUtilization }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Sales Invoices --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-receipt text-success me-1"></i> {{ __('sales.customers.card_invoices_history') }}</span>
                @can('sales.create')
                    @if(\Illuminate\Support\Facades\Route::has('sales-invoices.create'))
                        <a href="{{ route('sales-invoices.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-light border">
                            <i class="bi bi-plus-circle me-1"></i> {{ __('sales.customers.create_invoice') }}
                        </a>
                    @endif
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="num">Total (₹)</th>
                            <th class="num">Paid (₹)</th>
                            <th class="num">Balance Due (₹)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesInvoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('sales-invoices.show', $inv) }}" class="fw-bold text-dark text-decoration-none font-monospace">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ fmt_date($inv->invoice_date) }}</td>
                                <td>
                                    @if($inv->status === 'finalized')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Finalized</span>
                                    @elseif($inv->status === 'reversed')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Reversed</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Draft</span>
                                    @endif
                                </td>
                                <td class="num fw-semibold">{{ money($inv->total_amount) }}</td>
                                <td class="num text-success">{{ money($inv->paid_amount) }}</td>
                                <td class="num fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ money($inv->balance_due) }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('sales-invoices.show', $inv) }}" class="btn btn-sm btn-light" title="View Invoice">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(\Illuminate\Support\Facades\Route::has('sales-invoices.print'))
                                        <a href="{{ route('sales-invoices.print', $inv) }}" class="btn btn-sm btn-light" title="Print Bill" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-receipt-cutoff fs-2 d-block mb-1 text-secondary"></i>
                                    <span>{{ __('sales.customers.empty_invoices') }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Payment Receipts --}}
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-cash-stack text-success me-1"></i> {{ __('sales.customers.card_receipts_history') }}</span>
                @can('accounts.manage')
                    @if(\Illuminate\Support\Facades\Route::has('customer-receipts.create'))
                        <a href="{{ route('customer-receipts.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-light border">
                            <i class="bi bi-plus-circle me-1"></i> {{ __('sales.customers.record_receipt') }}
                        </a>
                    @endif
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference / Bank</th>
                            <th class="num">Amount (₹)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $rcpt)
                            <tr>
                                <td>
                                    <a href="{{ route('customer-receipts.show', $rcpt) }}" class="fw-bold text-dark text-decoration-none font-monospace">
                                        {{ $rcpt->receipt_number }}
                                    </a>
                                </td>
                                <td>{{ fmt_date($rcpt->receipt_date) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $rcpt->method_label ?? ucfirst($rcpt->payment_method) }}</span>
                                </td>
                                <td class="text-muted">
                                    {{ $rcpt->transaction_reference ?: ($rcpt->bank_name ?: '—') }}
                                </td>
                                <td class="num fw-bold text-success">{{ money($rcpt->amount) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('customer-receipts.show', $rcpt) }}" class="btn btn-sm btn-light" title="View Receipt">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(\Illuminate\Support\Facades\Route::has('customer-receipts.print'))
                                        <a href="{{ route('customer-receipts.print', $rcpt) }}" class="btn btn-sm btn-light" title="Print Receipt" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-wallet fs-2 d-block mb-1 text-secondary"></i>
                                    <span>{{ __('sales.customers.empty_receipts') }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
