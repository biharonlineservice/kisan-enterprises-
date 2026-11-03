@extends('layouts.app')

@section('title', 'Transfer ' . $transfer->transfer_number)

@section('content')
@php
    $estBags = (int) round(((float) $transfer->quantity_qtl) * 2);
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">{{ __('inventory.title') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stock-transfers.index') }}">{{ __('inventory.transfers.title') }}</a></li>
                <li class="breadcrumb-item active font-monospace">{{ $transfer->transfer_number }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h4 mb-0 fw-bold text-dark">{{ $transfer->transfer_number }}</h2>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                <i class="bi bi-check-circle-fill me-1"></i>{{ __('app.completed') }}
            </span>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('stock-transfers.challan', $transfer) }}" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-truck me-1"></i> {{ __('inventory.transfers.print_challan') }}
        </a>
        <a href="{{ route('stock-transfers.challan.pdf', $transfer) }}" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('inventory.transfers.pdf_challan') }}
        </a>
        <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('inventory.transfers.tab_all') }}
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        {{-- Transfer Route & Particulars Card --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title h6 mb-0 fw-bold text-dark">
                    <i class="bi bi-truck-flatbed text-primary me-2"></i>Inter-Warehouse Transfer Particulars
                </h5>
                <span class="small text-muted">
                    <i class="bi bi-calendar3 me-1"></i>{{ fmt_date($transfer->transfer_date, 'd M Y, h:i A') }}
                </span>
            </div>
            <div class="card-body">
                {{-- Route Visual Diagram --}}
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row align-items-center text-center text-md-start">
                        <div class="col-12 col-md-5 mb-2 mb-md-0">
                            <span class="badge bg-secondary text-white mb-1">{{ __('inventory.transfers.source_godown') }}</span>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-building me-1 text-secondary"></i>{{ $transfer->fromWarehouse->name }}
                            </h6>
                            <small class="text-muted font-monospace">Code: {{ $transfer->fromWarehouse->code }}</small>
                        </div>
                        <div class="col-12 col-md-2 my-2 my-md-0 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-white border rounded-circle shadow-sm" style="width: 44px; height: 44px;">
                                <i class="bi bi-arrow-right fs-5 text-primary"></i>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.72rem;">Transit Pass</div>
                        </div>
                        <div class="col-12 col-md-5 text-md-end">
                            <span class="badge bg-primary text-white mb-1">{{ __('inventory.transfers.dest_godown') }}</span>
                            <h6 class="fw-bold mb-0 text-primary">
                                <i class="bi bi-building me-1"></i>{{ $transfer->toWarehouse->name }}
                            </h6>
                            <small class="text-muted font-monospace">Code: {{ $transfer->toWarehouse->code }}</small>
                        </div>
                    </div>
                </div>

                {{-- Key Metric Indicators --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <div class="p-3 border rounded bg-white">
                            <span class="text-muted small d-block mb-1">{{ __('inventory.transfers.col_commodity') }}</span>
                            <div class="fw-bold text-dark fs-6">{{ $transfer->crop->name }}</div>
                            <small class="text-muted">{{ $transfer->cropVariety?->name ?? 'Standard / Mixed' }}</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 border rounded bg-white">
                            <span class="text-muted small d-block mb-1">{{ __('inventory.transfers.col_quantity_qtl') }}</span>
                            <div class="fw-bold text-success fs-5">{{ bc_round($transfer->quantity_qtl, 3) }} Qtl</div>
                            <small class="text-muted font-monospace">{{ bc_round($transfer->quantity_kg, 1) }} Kg</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded bg-white">
                            <span class="text-muted small d-block mb-1">{{ __('inventory.transfers.col_bags') }}</span>
                            <div class="fw-bold text-dark fs-5">~{{ number_format($estBags) }} Bags</div>
                            <small class="text-muted">Standard 50kg bags</small>
                        </div>
                    </div>
                </div>

                {{-- Transport Details --}}
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="bg-light text-muted fw-semibold" style="width: 30%;">Challan / Delivery Pass</th>
                            <td class="font-monospace fw-bold text-primary">{{ $transfer->challanNumber() }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light text-muted fw-semibold">Vehicle / Transport</th>
                            <td>
                                @if($transfer->vehicle_number)
                                    <span class="badge bg-light text-dark border font-monospace fs-6">
                                        <i class="bi bi-truck me-1"></i>{{ $transfer->vehicle_number }}
                                    </span>
                                @else
                                    <span class="text-muted">Direct Handover (Internal)</span>
                                @endif
                            </td>
                        </tr>
                        @if($transfer->notes)
                            <tr>
                                <th class="bg-light text-muted fw-semibold">Transfer Notes</th>
                                <td>{{ $transfer->notes }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        {{-- Audit & Security Information --}}
        <div class="card shadow-sm border mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0 fw-bold text-dark">
                    <i class="bi bi-shield-check text-success me-2"></i>Audit Information
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small d-block">Authorized Creator</label>
                    <div class="fw-semibold text-dark">
                        <i class="bi bi-person-circle me-1 text-primary"></i>{{ $transfer->creator?->name ?? 'System' }}
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">System Post Timestamp</label>
                    <div class="font-monospace text-dark">
                        {{ $transfer->created_at->format('d M Y, h:i:s A') }}
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Ledger Effect</label>
                    <div class="small text-muted">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">-{{ bc_round($transfer->quantity_qtl, 3) }} Qtl</span> from {{ $transfer->fromWarehouse->code }}<br>
                        <span class="badge bg-success-subtle text-success border border-success-subtle me-1 mt-1">+{{ bc_round($transfer->quantity_qtl, 3) }} Qtl</span> to {{ $transfer->toWarehouse->code }}
                    </div>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="{{ route('stock-transfers.challan', $transfer) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-printer me-1"></i> Print Transport Gate Pass
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
