@extends('layouts.app')

@section('title', 'Sales & Dispatch Reports')

@section('content')
<div class="page-toolbar">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports Hub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sales</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-1">Sales & Commercial Dispatch Report</h2>
        <p class="subtitle">Complete billing statement for rice mills, wholesalers, GST tax components, and receivables.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-excel"></i> Export CSV / Excel
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4 erp-filter-collapsible">
    <form method="GET" action="{{ route('reports.sales') }}" class="p-3 bg-light rounded" data-autosubmit>
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Date Preset</label>
                <select name="preset" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="today" {{ ($filters['preset'] ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ ($filters['preset'] ?? '') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ ($filters['preset'] ?? '') === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ ($filters['preset'] ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ ($filters['preset'] ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_quarter" {{ ($filters['preset'] ?? '') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="this_fy" {{ ($filters['preset'] ?? '') === 'this_fy' ? 'selected' : '' }}>This Financial Year</option>
                    <option value="all_time" {{ ($filters['preset'] ?? '') === 'all_time' ? 'selected' : '' }}>All Time</option>
                    <option value="custom" {{ ($filters['preset'] ?? '') === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            @if(($filters['preset'] ?? '') === 'custom')
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm">
                </div>
            @endif

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Customer / Mill</label>
                <select name="customer_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Customers & Mills</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ ($filters['customer_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="finalized" {{ ($filters['status'] ?? '') === 'finalized' ? 'selected' : '' }}>Finalized</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- KPI Summary Cards --}}
@php
    $sumQtl = '0';
    $sumBags = 0;
    $sumSubtotal = '0';
    $sumTax = '0';
    $sumNet = '0';
    $sumReceived = '0';
    $sumDue = '0';

    foreach($invoices as $inv) {
        if ($inv->status !== \App\Models\SalesInvoice::STATUS_CANCELLED) {
            foreach($inv->items as $item) {
                $sumQtl = bcadd($sumQtl, (string) $item->weight_qtl, 4);
                $sumBags += $item->bags;
            }
            $sumSubtotal = bcadd($sumSubtotal, (string) $inv->subtotal, 2);
            $sumTax = bcadd($sumTax, (string) $inv->tax_amount, 2);
            $sumNet = bcadd($sumNet, (string) $inv->net_amount, 2);
            $sumReceived = bcadd($sumReceived, (string) $inv->received_amount, 2);
            $sumDue = bcadd($sumDue, (string) $inv->balance_due, 2);
        }
    }
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Invoices Billed</small>
            <div class="fs-5 fw-bold text-dark text-nowrap my-1">{{ $invoices->count() }}</div>
            <small class="text-muted text-truncate d-block">{{ number_format($sumBags) }} Bags Dispatched</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Total Tonnage</small>
            <div class="fs-5 fw-bold text-primary text-nowrap my-1">{{ bc_round($sumQtl, 2) }} <span class="fs-6 fw-normal">Qtl</span></div>
            <small class="text-muted text-truncate d-block">{{ bc_round(bcdiv($sumQtl, '10', 3), 2) }} MT</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Taxable Subtotal</small>
            <div class="fs-5 fw-bold text-dark text-nowrap my-1">{{ money($sumSubtotal) }}</div>
            <small class="text-muted text-truncate d-block">Pre-tax revenue</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Total GST Tax</small>
            <div class="fs-5 fw-bold text-secondary text-nowrap my-1">{{ money($sumTax) }}</div>
            <small class="text-muted text-truncate d-block">CGST + SGST / IGST</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Total Realized</small>
            <div class="fs-5 fw-bold text-success text-nowrap my-1">{{ money($sumReceived) }}</div>
            <small class="text-muted text-truncate d-block">Collected to date</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Receivables Due</small>
            <div class="fs-5 fw-bold text-nowrap my-1 {{ bccomp($sumDue, '0', 2) > 0 ? 'text-danger' : 'text-muted' }}">
                {{ money($sumDue) }}
            </div>
            <small class="text-muted text-truncate d-block">Pending balance</small>
        </div>
    </div>
</div>

{{-- Report Tables (Tabs) --}}
<ul class="nav nav-tabs mb-3" id="salesReportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="sales-detailed-tab" data-bs-toggle="tab" data-bs-target="#sales-detailed" type="button" role="tab">
            <i class="bi bi-list-columns me-1"></i> Detailed Sales Invoices ({{ $invoices->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="sales-monthly-tab" data-bs-toggle="tab" data-bs-target="#sales-monthly" type="button" role="tab">
            <i class="bi bi-calendar3 me-1"></i> FY {{ $fyYear }}-{{ $fyYear + 1 }} Monthly Summary
        </button>
    </li>
</ul>

<div class="tab-content" id="salesReportContent">
    {{-- Detailed Invoices Register --}}
    <div class="tab-pane fade show active" id="sales-detailed" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer & GSTIN</th>
                            <th>Items & Crops</th>
                            <th class="text-end">Bags</th>
                            <th class="text-end">Weight (Qtl)</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Tax</th>
                            <th class="text-end">Net Billed</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Balance Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            @php
                                $invBags = $inv->items->sum('bags');
                                $invQtl = $inv->items->sum('weight_qtl');
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('sales-invoices.show', $inv) }}" class="fw-bold text-decoration-none">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="small text-muted">{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $inv->customer?->name }}</div>
                                    <small class="text-muted">{{ $inv->customer?->gstin ?? 'Unregistered' }}</small>
                                </td>
                                <td>
                                    @foreach($inv->items as $item)
                                        <div class="small">
                                            <strong>{{ $item->crop?->name }}</strong>
                                            <span class="text-muted">({{ $item->warehouse?->name }})</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="text-end">{{ $invBags }}</td>
                                <td class="text-end fw-semibold">{{ bc_round((string)$invQtl, 2) }}</td>
                                <td class="text-end">{{ money($inv->subtotal) }}</td>
                                <td class="text-end small text-muted">{{ money($inv->tax_amount) }}</td>
                                <td class="text-end fw-bold">{{ money($inv->net_amount) }}</td>
                                <td class="text-end text-success">{{ money($inv->received_amount) }}</td>
                                <td class="text-end fw-semibold {{ bccomp((string) $inv->balance_due, '0', 2) > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ money($inv->balance_due) }}
                                </td>
                                <td>
                                    @if($inv->status === 'finalized')
                                        <span class="badge bg-success-subtle text-success">Finalized</span>
                                    @elseif($inv->status === 'draft')
                                        <span class="badge bg-warning-subtle text-warning">Draft</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                    No sales invoices found matching selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Monthly FY Summary --}}
    <div class="tab-pane fade" id="sales-monthly" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold">Financial Year {{ $fyYear }}-{{ $fyYear + 1 }} Monthly Sales & Revenue Summary</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Invoices Billed</th>
                            <th class="text-end">Quantity (Qtl)</th>
                            <th class="text-end">Quantity (MT)</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Tax Amount</th>
                            <th class="text-end">Net Billed</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">Balance Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySummary as $row)
                            <tr>
                                <td class="fw-bold">{{ $row['month_name'] }}</td>
                                <td class="text-end">{{ $row['count'] }}</td>
                                <td class="text-end fw-semibold">{{ bc_round($row['quantity_qtl'], 2) }}</td>
                                <td class="text-end">{{ bc_round($row['quantity_mt'], 2) }}</td>
                                <td class="text-end">{{ money($row['subtotal']) }}</td>
                                <td class="text-end text-muted small">{{ money($row['tax_amount']) }}</td>
                                <td class="text-end fw-bold">{{ money($row['net_amount']) }}</td>
                                <td class="text-end text-success">{{ money($row['received_amount']) }}</td>
                                <td class="text-end fw-semibold {{ bccomp($row['balance_due'], '0', 2) > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ money($row['balance_due']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
