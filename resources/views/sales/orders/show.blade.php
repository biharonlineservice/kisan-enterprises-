@extends('layouts.app')

@section('title', 'Sales Order ' . $order->order_number)

@section('content')
{{-- 1. Header with Breadcrumbs & Action Bar --}}
<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div class="flex-grow-1" style="min-width: 0;">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('sales-orders.index') }}" class="text-success text-decoration-none">{{ __('sales.orders.title') }}</a></li>
                <li class="breadcrumb-item active font-monospace">{{ $order->order_number }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h2 class="h4 fw-bold mb-0 text-dark font-monospace">{{ $order->order_number }}</h2>
            @if($order->status === 'confirmed')
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="bi bi-check-circle me-1"></i>{{ __('sales.orders.status_confirmed') }}
                </span>
            @elseif($order->status === 'dispatched')
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                    <i class="bi bi-truck me-1"></i>{{ __('sales.orders.status_dispatched') }}
                </span>
            @elseif($order->status === 'completed')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-check2-all me-1"></i>{{ __('sales.orders.status_completed') }}
                </span>
            @elseif($order->status === 'cancelled')
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                    <i class="bi bi-x-circle me-1"></i>{{ __('sales.orders.status_cancelled') }}
                </span>
            @endif
            <span class="badge bg-light text-dark border">
                <i class="bi bi-calendar3 me-1 text-muted"></i>{{ fmt_date($order->order_date) }}
            </span>
        </div>
        <div class="small text-muted mt-1">
            <i class="bi bi-building me-1 text-secondary"></i><strong>{{ $order->customer?->name }}</strong>
            &bull; <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">{{ $order->customer?->type_name }}</span>
            @if($order->customer?->city)
                &bull; <i class="bi bi-geo-alt me-1 text-secondary"></i>{{ $order->customer->city }}
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
        <a href="{{ route('sales-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('sales.orders.tab_all') }}
        </a>
        <a href="{{ route('sales-orders.print', $order) }}" class="btn btn-sm btn-light border" target="_blank">
            <i class="bi bi-printer me-1"></i> {{ __('sales.orders.print_slip') }}
        </a>
        @can('sales.create')
            @if(in_array($order->status, ['confirmed', 'dispatched']))
                <a href="{{ route('sales-invoices.create', ['order_id' => $order->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-receipt me-1"></i> {{ __('sales.orders.generate_invoice') }}
                </a>
            @endif
        @endcan
        @can('sales.edit')
            @if(in_array($order->status, ['confirmed', 'dispatched']))
                <form action="{{ route('sales-orders.mark-completed', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this order as Completed / Buyer Delivered?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-check2-all me-1"></i> {{ __('sales.orders.mark_completed') }}
                    </button>
                </form>
            @endif
        @endcan
    </div>
</div>

{{-- 2. Sleek 3-Step Order Fulfillment Workflow Tracker Ribbon --}}
<div class="card mb-4 border shadow-sm">
    <div class="card-body p-3">
        <div class="row text-center g-2 align-items-center">
            {{-- Step 1 --}}
            <div class="col-4">
                <div class="d-flex flex-column align-items-center">
                    <div class="avatar bg-success text-white mb-1" style="width: 32px; height: 32px; font-size: 0.9rem;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <span class="small fw-bold text-dark">{{ __('sales.orders.step_confirmed') }}</span>
                    <small class="text-muted" style="font-size: 0.72rem;">{{ fmt_date($order->order_date) }}</small>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="col-4">
                <div class="d-flex flex-column align-items-center">
                    @php
                        $isDispatched = in_array($order->status, ['dispatched', 'completed']);
                    @endphp
                    <div class="avatar {{ $isDispatched ? 'bg-success text-white' : 'bg-warning-subtle text-warning-emphasis border border-warning' }} mb-1" style="width: 32px; height: 32px; font-size: 0.9rem;">
                        <i class="bi {{ $isDispatched ? 'bi-check-lg' : 'bi-hourglass-split' }}"></i>
                    </div>
                    <span class="small fw-bold text-dark">{{ __('sales.orders.step_invoiced') }}</span>
                    <small class="text-muted" style="font-size: 0.72rem;">
                        {{ number_format($progressPercent, 0) }}% fulfilled ({{ number_format($dispatchedQtl, 1) }} / {{ number_format($totalQtl, 1) }} Qtl)
                    </small>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="col-4">
                <div class="d-flex flex-column align-items-center">
                    @php
                        $isDone = $order->status === 'completed';
                    @endphp
                    <div class="avatar {{ $isDone ? 'bg-success text-white' : 'bg-light text-muted border' }} mb-1" style="width: 32px; height: 32px; font-size: 0.9rem;">
                        <i class="bi {{ $isDone ? 'bi-check-all' : 'bi-box-seam' }}"></i>
                    </div>
                    <span class="small fw-bold text-dark">{{ __('sales.orders.step_completed') }}</span>
                    <small class="text-muted" style="font-size: 0.72rem;">
                        {{ $isDone ? 'Settled & Delivered' : 'Pending final dispatch' }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column: Items & Invoices --}}
    <div class="col-12 col-lg-8">
        {{-- Ordered Commodities & Godown Allocation --}}
        <div class="card shadow-sm mb-4 border">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0 text-dark fw-bold">
                    <i class="bi bi-box-seam text-success me-1"></i> {{ __('sales.orders.items_section') }}
                </h5>
                <span class="badge bg-light text-dark border">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>Commodity &amp; Variety</th>
                            <th>Dispatch Godown</th>
                            <th class="num">Quantity (Qtl)</th>
                            <th class="num">Agreed Rate (₹/Qtl)</th>
                            <th class="num">Line Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->crop?->name }}</div>
                                    @if($item->cropVariety)
                                        <div class="text-muted small">{{ $item->cropVariety->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-building me-1 text-muted"></i>{{ $item->warehouse?->name }}
                                    </span>
                                </td>
                                <td class="num font-monospace fw-semibold">
                                    {{ bc_round($item->quantity_qtl, 3) }} Qtl
                                </td>
                                <td class="num font-monospace">
                                    {{ money($item->rate_per_qtl) }}
                                </td>
                                <td class="num font-monospace fw-bold text-dark">
                                    {{ money($item->amount) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Contracted Total:</td>
                            <td class="num font-monospace text-dark">{{ bc_round($order->total_qtl, 3) }} Qtl</td>
                            <td></td>
                            <td class="num font-monospace text-success fs-6">{{ money($order->total_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Dispatched Sales Invoices --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0 text-dark fw-bold">
                    <i class="bi bi-receipt text-success me-1"></i> Dispatched Sales Invoices &amp; Gate Passes
                </h5>
                @can('sales.create')
                    @if(in_array($order->status, ['confirmed', 'dispatched']))
                        <a href="{{ route('sales-invoices.create', ['order_id' => $order->id]) }}" class="btn btn-sm btn-light border">
                            <i class="bi bi-plus-circle me-1"></i> Generate Invoice
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
                            <th class="num">Total Amount</th>
                            <th class="num">Balance Due</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->invoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('sales-invoices.show', $inv) }}" class="fw-bold text-dark text-decoration-none font-monospace">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ fmt_date($inv->invoice_date) }}</td>
                                <td>
                                    @if($inv->status === 'finalized')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Dispatched</span>
                                        @if($inv->isDelivered())
                                            <span class="badge bg-success text-white ms-1" title="Delivered on {{ fmt_datetime($inv->delivered_at) }}">
                                                <i class="bi bi-check2-all"></i> Delivered
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle ms-1">
                                                <i class="bi bi-truck"></i> In-Transit
                                            </span>
                                        @endif
                                    @elseif($inv->status === 'reversed')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Reversed</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Draft</span>
                                    @endif
                                </td>
                                <td class="num font-monospace fw-semibold">{{ money($inv->total_amount) }}</td>
                                <td class="num font-monospace fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
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
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-truck fs-2 d-block mb-1 text-secondary"></i>
                                    <span>No dispatch invoices created for this order yet.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Column: Financial Exposure & Buyer Details --}}
    <div class="col-12 col-lg-4">
        {{-- Contract Execution & Fulfillment Summary Card --}}
        <div class="card shadow-sm mb-4 border">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0 text-dark fw-bold">
                    <i class="bi bi-pie-chart text-success me-1"></i> Fulfillment &amp; Value Summary
                </h5>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Total Booked Volume</span>
                    <span class="fw-bold font-monospace text-dark">{{ number_format($totalQtl, 3) }} Qtl</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Dispatched to Date</span>
                    <span class="fw-bold font-monospace text-success">{{ number_format($dispatchedQtl, 3) }} Qtl</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">Remaining to Dispatch</span>
                    <span class="fw-bold font-monospace text-warning-emphasis">{{ number_format($remainingQtl, 3) }} Qtl</span>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="text-muted">Fulfillment Progress</span>
                        <span class="fw-bold text-dark">{{ $progressPercent }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%;"
                             aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top">
                    <span class="small text-muted">Total Contract Value</span>
                    <span class="fw-bold text-success fs-6 font-monospace">{{ money($order->total_amount) }}</span>
                </div>
            </div>
        </div>

        {{-- Buyer / Mill Information Card --}}
        <div class="card shadow-sm mb-4 border">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0 text-dark fw-bold">
                    <i class="bi bi-building text-success me-1"></i> Buyer &amp; Destination
                </h5>
                <a href="{{ route('customers.show', $order->customer) }}" class="btn btn-sm btn-link text-success p-0 text-decoration-none">
                    Profile &rarr;
                </a>
            </div>
            <div class="card-body p-3">
                <h6 class="fw-bold mb-1 text-dark">{{ $order->customer?->name }}</h6>
                <div class="badge bg-light text-dark border mb-3">{{ $order->customer?->type_name }}</div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    @if($order->customer?->mobile)
                        <a href="tel:{{ $order->customer->mobile }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="bi bi-telephone-fill me-1"></i> {{ $order->customer->formatted_mobile ?? $order->customer->mobile }}
                        </a>
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $order->customer->mobile) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3" title="WhatsApp">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                    @endif
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted mb-2">Contact Person</dt>
                    <dd class="col-7 fw-semibold text-dark mb-2">{{ $order->customer?->contact_person ?: '—' }}</dd>

                    <dt class="col-5 text-muted mb-2">GSTIN</dt>
                    <dd class="col-7 font-monospace mb-2">{{ $order->customer?->gstin ?: 'Unregistered' }}</dd>

                    <dt class="col-5 text-muted mb-2">City &bull; State</dt>
                    <dd class="col-7 mb-2">{{ $order->customer?->city ?: '—' }}{{ $order->customer?->state ? ', ' . $order->customer->state : '' }}</dd>

                    <dt class="col-5 text-muted mb-2">Delivery Address</dt>
                    <dd class="col-7 text-dark mb-2">{{ $order->delivery_address ?: ($order->customer?->address ?: 'Standard mill gate') }}</dd>

                    @if($order->remarks)
                        <dt class="col-5 text-muted">Special Terms</dt>
                        <dd class="col-7 text-secondary fst-italic">{{ $order->remarks }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Audit & Sign-off Card --}}
        <div class="card shadow-sm border">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0 text-dark fw-bold">
                    <i class="bi bi-clock-history text-muted me-1"></i> Audit &amp; System Log
                </h5>
            </div>
            <div class="card-body p-3 small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded By:</span>
                    <span class="fw-semibold text-dark">{{ $order->creator?->name ?: 'System' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Recorded At:</span>
                    <span>{{ fmt_date($order->created_at, 'd M Y, h:i A') }}</span>
                </div>
                @if($order->updated_at && $order->updated_at != $order->created_at)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated:</span>
                        <span>{{ fmt_date($order->updated_at, 'd M Y, h:i A') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
