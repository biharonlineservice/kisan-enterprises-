<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Order — {{ $order->order_number }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #fff; color: #000; font-family: "Segoe UI", Arial, sans-serif; font-size: 14px; }
        .print-card { max-width: 820px; margin: 20px auto; padding: 25px; border: 1px solid #ddd; border-radius: 8px; }
        @media print {
            .no-print { display: none !important; }
            .print-card { border: 0; padding: 0; margin: 0; max-width: 100%; }
            body { font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center py-3 bg-light border-bottom">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">Print Sales Order</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
    </div>

    <div class="print-card">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
            <div>
                <h3 class="fw-bold mb-0 text-success">{{ setting('business.name', 'Kisan Enterprises') }}</h3>
                <small class="text-muted">{{ setting('business.tagline', 'Agricultural Crop Trading') }}</small>
                <div class="small text-muted mt-1">{{ setting('business.address') }} @if(setting('business.phone')) &bull; Ph: {{ setting('business.phone') }} @endif</div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark fs-6 px-3 py-2">SALES ORDER AGREEMENT</span>
                <div class="fw-bold fs-5 mt-1 text-primary font-monospace">{{ $order->order_number }}</div>
                <small class="text-muted">Date: {{ fmt_date($order->order_date) }}</small>
            </div>
        </div>

        {{-- Buyer Particulars --}}
        <div class="row g-3 mb-4">
            <div class="col-7">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block mb-1">BUYER / MILL PARTICULARS</small>
                    <div class="fw-bold fs-6">{{ $order->customer?->name }}</div>
                    <div class="small text-muted">{{ $order->customer?->type_name }}</div>
                    @if($order->customer?->contact_person)
                        <div class="small">Attn: <strong>{{ $order->customer->contact_person }}</strong></div>
                    @endif
                    @if($order->customer?->mobile)
                        <div class="small">Ph: {{ $order->customer->mobile }}</div>
                    @endif
                    @if($order->customer?->gstin)
                        <div class="small font-monospace">GSTIN: {{ $order->customer->gstin }}</div>
                    @endif
                    <div class="small mt-1">Delivery Destination: {{ $order->delivery_address ?: ($order->customer?->address ?: 'Mill Premises') }}</div>
                </div>
            </div>
            <div class="col-5">
                <div class="p-3 border rounded text-center bg-light h-100 d-flex flex-column justify-content-center">
                    <small class="text-muted d-block mb-1">Contract Status</small>
                    <span class="badge bg-{{ $order->status === 'confirmed' ? 'primary' : ($order->status === 'completed' ? 'success' : 'dark') }} px-3 py-1 mb-2">
                        {{ strtoupper($order->status) }}
                    </span>
                    <small class="text-muted d-block mb-1">Total Agreed Volume</small>
                    <div class="fw-bold fs-5 mb-1 font-monospace">{{ bc_round($order->total_qtl, 3) }} Qtl</div>
                    <small class="text-muted d-block mb-1">Total Contract Consideration</small>
                    <div class="fw-bold fs-5 text-success font-monospace">{{ money($order->total_amount) }}</div>
                </div>
            </div>
        </div>

        {{-- Line Items Table --}}
        <table class="table table-bordered table-sm mb-4">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Commodity & Variety</th>
                    <th>Dispatch Godown</th>
                    <th class="text-end" style="width: 18%;">Quantity (Qtl)</th>
                    <th class="text-end" style="width: 18%;">Rate (₹/Qtl)</th>
                    <th class="text-end" style="width: 20%;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->crop?->name }}</strong>
                            @if($item->cropVariety)
                                <span class="text-muted">({{ $item->cropVariety->name }})</span>
                            @endif
                        </td>
                        <td>{{ $item->warehouse?->name }}</td>
                        <td class="text-end font-monospace">{{ bc_round($item->quantity_qtl, 3) }}</td>
                        <td class="text-end font-monospace">{{ money($item->rate_per_qtl) }}</td>
                        <td class="text-end font-monospace fw-bold">{{ money($item->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end">Total Contracted:</td>
                    <td class="text-end font-monospace">{{ bc_round($order->total_qtl, 3) }} Qtl</td>
                    <td></td>
                    <td class="text-end font-monospace text-success fs-6">{{ money($order->total_amount) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($order->remarks)
            <div class="p-2 border rounded mb-4 small bg-light">
                <strong>Special Terms & Conditions:</strong> {{ $order->remarks }}
            </div>
        @endif

        {{-- Signatures --}}
        <div class="mt-5 pt-4 border-top d-flex justify-content-between">
            <div class="text-center" style="width: 220px;">
                <div class="border-top border-dark pt-1 small">Buyer Stamp & Signature</div>
            </div>
            <div class="text-center" style="width: 220px;">
                <div class="border-top border-dark pt-1 small">Authorized Signatory<br>{{ setting('business.name') }}</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top" style="font-size: 11px;">
            Recorded in Kisan Enterprises ERP by {{ $order->creator?->name ?? 'System' }} &bull; Printed on {{ now()->format('d-m-Y h:i A') }}
        </div>
    </div>
</body>
</html>

