@extends('layouts.app')

@section('title', 'Inventory & Storage Reports')

@section('content')
<div class="page-toolbar">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports Hub</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventory & Storage</li>
            </ol>
        </nav>
        <h2 class="h5 fw-bold mb-1">Inventory & Storage Reports</h2>
        <p class="subtitle">Real-time stock valuation across storage godowns, movement audit trail, and warehouse capacity utilization.</p>
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
        <a class="nav-link {{ $tab === 'balance' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.inventory', ['tab' => 'balance']) }}">
            <i class="bi bi-box-seam me-1"></i> Current Stock Balances
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'movements' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.inventory', ['tab' => 'movements']) }}">
            <i class="bi bi-arrow-left-right me-1"></i> Stock Movement Register
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'capacity' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.inventory', ['tab' => 'capacity']) }}">
            <i class="bi bi-building-check me-1"></i> Warehouse Capacity Utilization
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'low_stock' ? 'active' : 'bg-white border text-dark' }} fw-semibold" href="{{ route('reports.inventory', ['tab' => 'low_stock']) }}">
            <i class="bi bi-exclamation-triangle me-1"></i> Low Stock Alerts ({{ $lowStock->count() }})
        </a>
    </li>
</ul>

@if($tab === 'balance')
    {{-- Current Stock Balance --}}
    <div class="card mb-3">
        <form method="GET" action="{{ route('reports.inventory') }}" class="p-3 bg-light rounded" data-autosubmit>
            <input type="hidden" name="tab" value="balance">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Warehouse</label>
                    <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Crop</label>
                    <select name="crop_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Crops</option>
                        @foreach($crops as $c)
                            <option value="{{ $c->id }}" {{ ($filters['crop_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('reports.inventory', ['tab' => 'balance']) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Warehouse / Godown</th>
                        <th>Crop</th>
                        <th>Variety</th>
                        <th class="text-end">Stock Bags</th>
                        <th class="text-end">Stock Weight (Qtl)</th>
                        <th class="text-end">Stock Weight (MT)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totBags = 0;
                        $totQtl = '0';
                    @endphp
                    @forelse($stockBalances as $b)
                        @php
                            $totBags += $b['bags'];
                            $totQtl = bcadd($totQtl, (string) $b['weight_qtl'], 4);
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $b['warehouse']?->name }}</td>
                            <td>{{ $b['crop']?->name }}</td>
                            <td>{{ $b['variety']?->name ?? 'Standard Commercial Grade' }}</td>
                            <td class="text-end">{{ number_format($b['bags']) }}</td>
                            <td class="text-end fw-bold text-success">{{ bc_round($b['weight_qtl'], 2) }}</td>
                            <td class="text-end text-muted">{{ bc_round($b['weight_mt'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-2 text-muted d-block mb-2"></i>
                                No active stock balance found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($stockBalances->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total Stock on Hand Across Godowns</td>
                            <td class="text-end">{{ number_format($totBags) }}</td>
                            <td class="text-end text-success">{{ bc_round($totQtl, 2) }} Qtl</td>
                            <td class="text-end">{{ bc_round(bcdiv($totQtl, '10', 3), 2) }} MT</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

@elseif($tab === 'movements')
    {{-- Movements Register --}}
    <div class="card mb-3">
        <form method="GET" action="{{ route('reports.inventory') }}" class="p-3 bg-light rounded" data-autosubmit>
            <input type="hidden" name="tab" value="movements">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date Preset</label>
                    <select name="preset" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="today" {{ ($filters['preset'] ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ ($filters['preset'] ?? '') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ ($filters['preset'] ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ ($filters['preset'] ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_fy" {{ ($filters['preset'] ?? '') === 'this_fy' ? 'selected' : '' }}>This Financial Year</option>
                        <option value="all_time" {{ ($filters['preset'] ?? '') === 'all_time' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Warehouse</label>
                    <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Crop</label>
                    <select name="crop_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Crops</option>
                        @foreach($crops as $c)
                            <option value="{{ $c->id }}" {{ ($filters['crop_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Movement Type</label>
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="inward" {{ ($filters['type'] ?? '') === 'inward' ? 'selected' : '' }}>Inward (Purchase)</option>
                        <option value="outward" {{ ($filters['type'] ?? '') === 'outward' ? 'selected' : '' }}>Outward (Sale)</option>
                        <option value="transfer_in" {{ ($filters['type'] ?? '') === 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                        <option value="transfer_out" {{ ($filters['type'] ?? '') === 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                        <option value="adjustment" {{ ($filters['type'] ?? '') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('reports.inventory', ['tab' => 'movements']) }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Warehouse</th>
                        <th>Crop & Variety</th>
                        <th class="text-end">Bags In</th>
                        <th class="text-end">Bags Out</th>
                        <th class="text-end">Weight In (Qtl)</th>
                        <th class="text-end">Weight Out (Qtl)</th>
                        <th>Reference Doc</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td class="small text-muted">{{ $m->transaction_date->format('d M Y') }}</td>
                            <td>
                                @if(in_array($m->transaction_type, ['inward', 'transfer_in']))
                                    <span class="badge bg-success-subtle text-success text-capitalize">{{ str_replace('_', ' ', $m->transaction_type) }}</span>
                                @elseif(in_array($m->transaction_type, ['outward', 'transfer_out']))
                                    <span class="badge bg-danger-subtle text-danger text-capitalize">{{ str_replace('_', ' ', $m->transaction_type) }}</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning text-capitalize">{{ $m->transaction_type }}</span>
                                @endif
                            </td>
                            <td>{{ $m->warehouse?->name }}</td>
                            <td>
                                <div>{{ $m->crop?->name }}</div>
                                <small class="text-muted">{{ $m->variety?->name ?? 'Standard' }}</small>
                            </td>
                            <td class="text-end text-success">{{ $m->bags_in ?: '—' }}</td>
                            <td class="text-end text-danger">{{ $m->bags_out ?: '—' }}</td>
                            <td class="text-end fw-semibold text-success">{{ $m->weight_qtl_in > 0 ? bc_round((string)$m->weight_qtl_in, 2) : '—' }}</td>
                            <td class="text-end fw-semibold text-danger">{{ $m->weight_qtl_out > 0 ? bc_round((string)$m->weight_qtl_out, 2) : '—' }}</td>
                            <td class="small text-muted">
                                {{ class_basename($m->reference_type) }} #{{ $m->reference_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                No stock movements recorded for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($tab === 'capacity')
    {{-- Warehouse Capacity Utilization --}}
    <div class="row g-4">
        @foreach($capacity as $item)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold">{{ $item['warehouse']->name }}</h6>
                        <span class="badge bg-light text-dark border">{{ $item['warehouse']->code }}</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <span class="text-muted small">Storage Capacity:</span>
                            <span class="fw-bold">{{ bc_round($item['capacity_qtl'], 0) }} Qtl</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <span class="text-muted small">Current Occupied:</span>
                            <span class="fw-bold text-success">{{ bc_round($item['current_stock_qtl'], 2) }} Qtl ({{ bc_round($item['current_stock_mt'], 2) }} MT)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span class="text-muted small">Available Space:</span>
                            <span class="fw-bold text-dark">{{ bc_round($item['available_qtl'], 2) }} Qtl</span>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Utilization</span>
                                <span class="fw-bold">{{ $item['utilization_percent'] }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                @php
                                    $pct = (float) $item['utilization_percent'];
                                    $barClass = $pct > 90 ? 'bg-danger' : ($pct > 75 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ min(100, $pct) }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        @if($item['warehouse']->location)
                            <div class="small text-muted mt-3">
                                <i class="bi bi-geo-alt me-1"></i> {{ $item['warehouse']->location }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@elseif($tab === 'low_stock')
    {{-- Low Stock Alerts --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                Low Stock Threshold Alerts (Safety level: 50.0 Quintals)
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Crop Commodity</th>
                        <th>Code / Unit</th>
                        <th class="text-end">Current Stock (Qtl)</th>
                        <th class="text-end">Safety Threshold</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStock as $alert)
                        <tr>
                            <td class="fw-bold">{{ $alert['crop']->name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $alert['crop']->code }}</span> &bull; {{ $alert['crop']->unit }}</td>
                            <td class="text-end fw-bold text-danger">{{ bc_round($alert['current_stock_qtl'], 2) }} Qtl</td>
                            <td class="text-end text-muted">{{ $alert['threshold_qtl'] }} Qtl</td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger">Re-order / Procurement Required</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                                All agricultural commodities are adequately stocked above safety threshold.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
