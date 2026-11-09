@extends('layouts.app')

@section('title', 'Financial & Outstanding Reports')

@section('content')
<div class="page-toolbar">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports Hub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Finance & Balances</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-1">Financial & Outstanding Reports</h2>
        <p class="subtitle">Aging farmer payables, customer credit receivables, expense breakdowns, and crop profitability.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv', 'tab' => $tab]) }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-excel"></i> Export CSV / Excel
        </a>
    </div>
</div>

{{-- Navigation Tabs --}}
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'outstanding' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.finance', ['tab' => 'outstanding']) }}">
            <i class="bi bi-person-exclamation me-1"></i> Farmer Payables (Aging)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'customer_outstanding' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.finance', ['tab' => 'customer_outstanding']) }}">
            <i class="bi bi-building-exclamation me-1"></i> Customer Receivables (Aging)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'expenses' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.finance', ['tab' => 'expenses']) }}">
            <i class="bi bi-tags me-1"></i> Expense Analysis
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'statement' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.finance', ['tab' => 'statement', 'account_id' => $accounts->first()?->id]) }}">
            <i class="bi bi-bank me-1"></i> Cash & Bank Statements
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'profitability' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.finance', ['tab' => 'profitability']) }}">
            <i class="bi bi-graph-up-arrow me-1"></i> Crop-wise Profitability
        </a>
    </li>
</ul>

@if($tab === 'outstanding')
    {{-- Farmer Payables with Aging --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold">Farmer Unpaid Balances with Aging Breakdown</h6>
            <span class="badge bg-danger-subtle text-danger fs-6">{{ $farmerOutstanding->count() }} Farmers Due</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Farmer</th>
                        <th>Village & Tehsil</th>
                        <th class="text-center">Unpaid Lots</th>
                        <th class="text-end">Total Due</th>
                        <th class="text-end text-muted">0&ndash;15 Days</th>
                        <th class="text-end text-muted">16&ndash;30 Days</th>
                        <th class="text-end text-warning">31&ndash;60 Days</th>
                        <th class="text-end text-danger">60+ Days</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumTotal = '0'; @endphp
                    @forelse($farmerOutstanding as $row)
                        @php $sumTotal = bcadd($sumTotal, $row['total_outstanding'], 2); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('farmers.show', $row['farmer']) }}" class="fw-bold text-decoration-none">
                                    {{ $row['farmer']->name }}
                                </a>
                                <div class="small text-muted">{{ $row['farmer']->farmer_id }} &bull; {{ $row['farmer']->mobile }}</div>
                            </td>
                            <td class="small">{{ $row['farmer']->village }}, {{ $row['farmer']->tehsil }}</td>
                            <td class="text-center">{{ $row['purchase_count'] }}</td>
                            <td class="text-end fw-bold text-danger">{{ money($row['total_outstanding']) }}</td>
                            <td class="text-end small">{{ money($row['aging']['0_15'], false) }}</td>
                            <td class="text-end small">{{ money($row['aging']['16_30'], false) }}</td>
                            <td class="text-end small text-warning fw-semibold">{{ money($row['aging']['31_60'], false) }}</td>
                            <td class="text-end small text-danger fw-bold">{{ money($row['aging']['60_plus'], false) }}</td>
                            <td class="text-end">
                                <a href="{{ route('farmer-payments.create', ['farmer_id' => $row['farmer']->id]) }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-cash"></i> Pay Now
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                                All farmer purchases are fully paid. Zero outstanding liabilities!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($farmerOutstanding->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total Farmer Outstanding Liabilities</td>
                            <td class="text-end text-danger">{{ money($sumTotal) }}</td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@elseif($tab === 'customer_outstanding')
    {{-- Customer Receivables with Aging & Credit Utilization --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold">Customer Outstanding Invoices & Credit Utilization</h6>
            <span class="badge bg-danger-subtle text-danger fs-6">{{ $customerOutstanding->count() }} Customers Due</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer / Mill</th>
                        <th class="text-end">Credit Limit</th>
                        <th class="text-center">Overdue Invoices</th>
                        <th class="text-end">Total Due</th>
                        <th class="text-end">Credit Used</th>
                        <th class="text-end text-muted">0&ndash;15 Days</th>
                        <th class="text-end text-muted">16&ndash;30 Days</th>
                        <th class="text-end text-warning">31&ndash;60 Days</th>
                        <th class="text-end text-danger">60+ Days</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumCustDue = '0'; @endphp
                    @forelse($customerOutstanding as $row)
                        @php $sumCustDue = bcadd($sumCustDue, $row['total_outstanding'], 2); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('customers.show', $row['customer']) }}" class="fw-bold text-decoration-none">
                                    {{ $row['customer']->name }}
                                </a>
                                <div class="small text-muted">{{ $row['customer']->phone }} &bull; {{ $row['customer']->contact_person }}</div>
                            </td>
                            <td class="text-end">{{ money($row['credit_limit']) }}</td>
                            <td class="text-center">{{ $row['invoice_count'] }}</td>
                            <td class="text-end fw-bold text-danger">{{ money($row['total_outstanding']) }}</td>
                            <td class="text-end">
                                @if($row['credit_utilization'] !== null)
                                    <span class="badge {{ (float) $row['credit_utilization'] > 100 ? 'bg-danger' : ((float) $row['credit_utilization'] > 80 ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">
                                        {{ $row['credit_utilization'] }}%
                                    </span>
                                @else
                                    <span class="text-muted small">No limit</span>
                                @endif
                            </td>
                            <td class="text-end small">{{ money($row['aging']['0_15'], false) }}</td>
                            <td class="text-end small">{{ money($row['aging']['16_30'], false) }}</td>
                            <td class="text-end small text-warning fw-semibold">{{ money($row['aging']['31_60'], false) }}</td>
                            <td class="text-end small text-danger fw-bold">{{ money($row['aging']['60_plus'], false) }}</td>
                            <td class="text-end">
                                <a href="{{ route('customer-receipts.create', ['customer_id' => $row['customer']->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-wallet2"></i> Receive
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                                All customer invoices are fully settled. Zero outstanding receivables!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($customerOutstanding->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total Customer Receivables Outstanding</td>
                            <td class="text-end text-danger">{{ money($sumCustDue) }}</td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@elseif($tab === 'expenses')
    {{-- Expense Breakdown by Category --}}
    <div class="card mb-3 erp-filter-collapsible">
        <form method="GET" action="{{ route('reports.finance') }}" class="p-3 bg-light rounded" data-autosubmit>
            <input type="hidden" name="tab" value="expenses">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">Period Preset</label>
                    <select name="preset" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="this_month" {{ ($filters['preset'] ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ ($filters['preset'] ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_quarter" {{ ($filters['preset'] ?? '') === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="this_fy" {{ ($filters['preset'] ?? '') === 'this_fy' ? 'selected' : '' }}>This Financial Year</option>
                        <option value="all_time" {{ ($filters['preset'] ?? '') === 'all_time' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1"><i class="bi bi-funnel"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Expense Category</th>
                        <th class="text-center">Vouchers Count</th>
                        <th class="text-end">Total Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumExp = '0'; @endphp
                    @forelse($expenseSummary as $cat)
                        @php $sumExp = bcadd($sumExp, $cat['total_amount'], 2); @endphp
                        <tr>
                            <td class="fw-bold">{{ $cat['category']?->name ?? 'Uncategorized' }}</td>
                            <td class="text-center">{{ $cat['count'] }}</td>
                            <td class="text-end fw-bold text-dark">{{ money($cat['total_amount']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                No expenses recorded for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($expenseSummary->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total Operating Expenses</td>
                            <td class="text-center">{{ $expenseSummary->sum('count') }}</td>
                            <td class="text-end">{{ money($sumExp) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@elseif($tab === 'statement')
    {{-- Cashbook / Bankbook Statement --}}
    <div class="card mb-3 erp-filter-collapsible">
        <form method="GET" action="{{ route('reports.finance') }}" class="p-3 bg-light rounded" data-autosubmit>
            <input type="hidden" name="tab" value="statement">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold">Select Account</label>
                    <select name="account_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($filters['account_id'] ?? $accounts->first()?->id) == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }} ({{ ucfirst($acc->account_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold">Date Preset</label>
                    <select name="preset" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="today" {{ ($filters['preset'] ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ ($filters['preset'] ?? '') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ ($filters['preset'] ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ ($filters['preset'] ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_fy" {{ ($filters['preset'] ?? '') === 'this_fy' ? 'selected' : '' }}>This Financial Year</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1"><i class="bi bi-funnel"></i> View Statement</button>
                </div>
            </div>
        </form>
    </div>

    @if($accountStatement)
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-white border-0 shadow-sm p-3">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Account Name</small>
                    <div class="fs-5 fw-bold text-dark">{{ $accountStatement['account']->name }}</div>
                    <small class="text-muted text-capitalize">{{ $accountStatement['account']->account_type }} Account</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-white border-0 shadow-sm p-3">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Period Inflow</small>
                    <div class="fs-5 fw-bold text-success">+{{ money($accountStatement['total_inflow']) }}</div>
                    <small class="text-muted">Receipts & Credits</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-white border-0 shadow-sm p-3">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Period Outflow</small>
                    <div class="fs-5 fw-bold text-danger">-{{ money($accountStatement['total_outflow']) }}</div>
                    <small class="text-muted">Payments & Expenses</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-white border-0 shadow-sm p-3">
                    <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Current Live Balance</small>
                    <div class="fs-5 fw-bold text-primary">{{ money($accountStatement['current_balance']) }}</div>
                    <small class="text-muted">Available liquidity</small>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">Date</th>
                            <th>Voucher / Ref</th>
                            <th>Particulars</th>
                            <th class="text-end text-success" style="width: 140px;">Inflow (₹)</th>
                            <th class="text-end text-danger" style="width: 140px;">Outflow (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountStatement['lines'] as $line)
                            <tr>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($line['date'])->format('d M Y') }}</td>
                                <td class="fw-semibold">{{ $line['voucher_no'] }}</td>
                                <td>{{ $line['particulars'] }}</td>
                                <td class="text-end text-success fw-semibold">
                                    {{ bccomp($line['inflow'], '0', 2) > 0 ? money($line['inflow']) : '—' }}
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    {{ bccomp($line['outflow'], '0', 2) > 0 ? money($line['outflow']) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No voucher transactions found for this account in the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@elseif($tab === 'profitability')
    {{-- Crop Profitability --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="card-title mb-0 fw-bold">Commodity Gross Margin & Profitability Analysis</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Crop Commodity</th>
                        <th class="text-end">Procured (Qtl)</th>
                        <th class="text-end">Procurement Cost (₹)</th>
                        <th class="text-end">Sold (Qtl)</th>
                        <th class="text-end">Sales Revenue (₹)</th>
                        <th class="text-end">Gross Margin (₹)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cropProfitability as $row)
                        <tr>
                            <td class="fw-bold">{{ $row['crop']->name }}</td>
                            <td class="text-end">{{ bc_round($row['purchased_qtl'], 2) }}</td>
                            <td class="text-end">{{ money($row['purchase_cost']) }}</td>
                            <td class="text-end">{{ bc_round($row['sold_qtl'], 2) }}</td>
                            <td class="text-end fw-semibold text-primary">{{ money($row['sales_revenue']) }}</td>
                            <td class="text-end fw-bold {{ bccomp($row['gross_margin'], '0', 2) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ money($row['gross_margin']) }}
                            </td>
                            <td class="text-center">
                                @if(bccomp($row['gross_margin'], '0', 2) > 0)
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-arrow-up"></i> Profitable</span>
                                @elseif(bccomp($row['gross_margin'], '0', 2) < 0)
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-arrow-down"></i> Loss</span>
                                @else
                                    <span class="badge bg-light text-muted border">Neutral / Holding</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No procurement or sales recorded for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
