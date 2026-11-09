@extends('layouts.app')

@section('title', 'Purchase & Procurement Reports')

@section('content')
<div class="page-toolbar">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports Hub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchases</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-1">Procurement & Purchases Report</h2>
        <p class="subtitle">Comprehensive register of crop purchases, weighbridge weights, rates, and farmer settlement balances.</p>
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
    <form method="GET" action="{{ route('reports.purchases') }}" class="p-3 bg-light rounded" data-autosubmit>
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

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold">Crop</label>
                <select name="crop_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Crops</option>
                    @foreach($crops as $c)
                        <option value="{{ $c->id }}" {{ ($filters['crop_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold">Warehouse</label>
                <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold">Farmer</label>
                <select name="farmer_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Farmers</option>
                    @foreach($farmers as $f)
                        <option value="{{ $f->id }}" {{ ($filters['farmer_id'] ?? '') == $f->id ? 'selected' : '' }}>{{ $f->name }} ({{ $f->farmer_id }})</option>
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
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('reports.purchases') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </div>
    </form>
</div>

{{-- KPI Summary Cards --}}
@php
    $sumQtl = '0';
    $sumBags = 0;
    $sumGross = '0';
    $sumNet = '0';
    $sumPaid = '0';
    $sumDue = '0';

    foreach($purchases as $p) {
        if ($p->status !== \App\Models\Purchase::STATUS_CANCELLED) {
            $sumQtl = bcadd($sumQtl, (string) $p->net_weight_qtl, 4);
            $sumBags += $p->bags;
            $sumGross = bcadd($sumGross, (string) $p->gross_amount, 2);
            $sumNet = bcadd($sumNet, (string) $p->net_payable, 2);
            $sumPaid = bcadd($sumPaid, (string) $p->paid_amount, 2);
            $sumDue = bcadd($sumDue, (string) $p->balance_due, 2);
        }
    }
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Procured Lots</small>
            <div class="fs-5 fw-bold text-dark text-nowrap my-1">{{ $purchases->count() }}</div>
            <small class="text-muted text-truncate d-block">{{ number_format($sumBags) }} Bags</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Net Quantity</small>
            <div class="fs-5 fw-bold text-success text-nowrap my-1">{{ bc_round($sumQtl, 2) }} <span class="fs-6 fw-normal">Qtl</span></div>
            <small class="text-muted text-truncate d-block">{{ bc_round(bcdiv($sumQtl, '10', 3), 2) }} MT</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Gross Value</small>
            <div class="fs-5 fw-bold text-dark text-nowrap my-1">{{ money($sumGross) }}</div>
            <small class="text-muted text-truncate d-block">Before deductions</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Net Payable</small>
            <div class="fs-5 fw-bold text-primary text-nowrap my-1">{{ money($sumNet) }}</div>
            <small class="text-muted text-truncate d-block">Farmer liability</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Amount Paid</small>
            <div class="fs-5 fw-bold text-success text-nowrap my-1">{{ money($sumPaid) }}</div>
            <small class="text-muted text-truncate d-block">Settled to date</small>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 bg-white shadow-sm p-3 h-100 rounded-3">
            <small class="text-muted text-uppercase fw-semibold text-truncate d-block" style="font-size: 0.7rem;">Outstanding Due</small>
            <div class="fs-5 fw-bold text-nowrap my-1 {{ bccomp($sumDue, '0', 2) > 0 ? 'text-danger' : 'text-muted' }}">
                {{ money($sumDue) }}
            </div>
            <small class="text-muted text-truncate d-block">Pending balance</small>
        </div>
    </div>
</div>

{{-- Report Tables (Tabs) --}}
<ul class="nav nav-tabs mb-3" id="purchaseReportTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active fw-semibold" id="detailed-tab" data-bs-toggle="tab" data-bs-target="#detailed" type="button" role="tab">
            <i class="bi bi-list-columns me-1"></i> Detailed Purchases Register ({{ $purchases->count() }})
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link fw-semibold" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab">
            <i class="bi bi-calendar3 me-1"></i> FY {{ $fyYear }}-{{ $fyYear + 1 }} Monthly Summary
        </button>
    </li>
</ul>

<div class="tab-content" id="purchaseReportContent">
    {{-- Detailed Purchases Register --}}
    <div class="tab-pane fade show active" id="detailed" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Purchase ID</th>
                            <th>Date</th>
                            <th>Farmer</th>
                            <th>Crop / Variety</th>
                            <th>Warehouse</th>
                            <th class="text-end">Bags</th>
                            <th class="text-end">Net Wt (Qtl)</th>
                            <th class="text-end">Rate / Qtl</th>
                            <th class="text-end">Net Payable</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('purchases.show', $p) }}" class="fw-bold text-decoration-none">
                                        {{ $p->purchase_id }}
                                    </a>
                                </td>
                                <td class="small text-muted">{{ $p->purchase_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $p->farmer?->name }}</div>
                                    <small class="text-muted">{{ $p->farmer?->village }} ({{ $p->farmer?->farmer_id }})</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $p->crop?->name }}</div>
                                    <small class="text-muted">{{ $p->variety?->name ?? 'Standard' }}</small>
                                </td>
                                <td class="small">{{ $p->warehouse?->name }}</td>
                                <td class="text-end">{{ $p->bags }}</td>
                                <td class="text-end fw-semibold">{{ bc_round($p->net_weight_qtl, 2) }}</td>
                                <td class="text-end">{{ money($p->rate_per_qtl, false) }}</td>
                                <td class="text-end fw-bold">{{ money($p->net_payable) }}</td>
                                <td class="text-end text-success">{{ money($p->paid_amount) }}</td>
                                <td class="text-end fw-semibold {{ bccomp((string) $p->balance_due, '0', 2) > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ money($p->balance_due) }}
                                </td>
                                <td>
                                    @if($p->status === 'finalized')
                                        <span class="badge bg-success-subtle text-success">Finalized</span>
                                    @elseif($p->status === 'draft')
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
                                    No purchase records found matching selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Monthly FY Summary --}}
    <div class="tab-pane fade" id="monthly" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold">Financial Year {{ $fyYear }}-{{ $fyYear + 1 }} Monthly Procurement Summary</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Lots Procured</th>
                            <th class="text-end">Quantity (Qtl)</th>
                            <th class="text-end">Quantity (MT)</th>
                            <th class="text-end">Gross Amount</th>
                            <th class="text-end">Net Payable</th>
                            <th class="text-end">Paid Amount</th>
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
                                <td class="text-end">{{ money($row['gross_amount']) }}</td>
                                <td class="text-end fw-bold">{{ money($row['net_payable']) }}</td>
                                <td class="text-end text-success">{{ money($row['paid_amount']) }}</td>
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
