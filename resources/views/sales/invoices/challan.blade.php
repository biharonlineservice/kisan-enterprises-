<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('sales.invoices.transport_challan', ['default' => 'Delivery / Transport Challan']) }} — {{ $invoice->challanNumber() }}</title>
    <style>
        * { box-sizing: border-box; }
        @font-face {
            font-family: 'DevanagariFont';
            src: url('{{ public_path('fonts/Mangal.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'DevanagariFont';
            src: url('{{ public_path('fonts/Mangal-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        body {
            font-family: 'DevanagariFont', 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #24332A;
            font-size: 13px;
            line-height: 1.4;
            margin: 0;
            padding: {{ isset($isPdf) && $isPdf ? '0' : '20px' }};
            background: {{ isset($isPdf) && $isPdf ? '#ffffff' : '#f6f8f6' }};
        }
        .sheet {
            background: #ffffff;
            max-width: 820px;
            margin: 0 auto;
            padding: 28px;
            border: {{ isset($isPdf) && $isPdf ? 'none' : '1px solid #e3e9e4' }};
            border-radius: {{ isset($isPdf) && $isPdf ? '0' : '6px' }};
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #21864B;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #155B36;
            margin-bottom: 3px;
        }
        .company-tagline {
            font-size: 11px;
            color: #738078;
            margin-bottom: 4px;
        }
        .company-contact {
            font-size: 11px;
            color: #555;
            line-height: 1.35;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #21864B;
            text-transform: uppercase;
            text-align: right;
            margin-bottom: 3px;
        }
        .doc-subtitle {
            font-size: 10px;
            color: #666;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .doc-meta {
            text-align: right;
            font-size: 11px;
            color: #333;
            line-height: 1.45;
        }
        .copies-bar {
            background: #f1f5f2;
            border: 1px solid #dce5df;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            color: #3e5045;
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .transport-badge-bar {
            background: #eaf5ed;
            border: 1px solid #c2e2cc;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .transport-item {
            font-size: 12px;
        }
        .transport-item strong {
            color: #155B36;
            font-size: 13px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .info-panel {
            background: #fbfcfb;
            border: 1px solid #e3e9e4;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 11px;
            vertical-align: top;
        }
        .panel-heading {
            font-size: 10px;
            font-weight: bold;
            color: #155B36;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #c2e2cc;
            padding-bottom: 4px;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        table.items-table th, table.items-table td {
            border: 1px solid #e3e9e4;
            padding: 7px 10px;
            font-size: 12px;
        }
        table.items-table th {
            background: #f1f5f2;
            color: #24332A;
            font-weight: bold;
            text-align: left;
        }
        table.items-table td.text-end, table.items-table th.text-end {
            text-align: right;
        }
        table.items-table td.text-center, table.items-table th.text-center {
            text-align: center;
        }
        .instructions-box {
            background: #fdfdfd;
            border: 1px dashed #d0d7d2;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 10px;
            font-size: 10.5px;
            color: #555;
            line-height: 1.45;
        }
        .signatures {
            margin-top: 35px;
            width: 100%;
            border-collapse: collapse;
        }
        .sig-box {
            text-align: center;
            vertical-align: bottom;
            padding-top: 35px;
            width: 33.33%;
        }
        .sig-line {
            border-top: 1px solid #24332A;
            width: 80%;
            margin: 0 auto 4px auto;
        }
        .sig-label {
            font-size: 10.5px;
            color: #444;
            font-weight: bold;
        }
        .sig-sub {
            font-size: 9.5px;
            color: #777;
        }
        .footer-note {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px dashed #e3e9e4;
            text-align: center;
            font-size: 10px;
            color: #738078;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; padding: 0; }
            .sheet { border: none; padding: 10px; max-width: 100%; }
        }
    </style>
</head>
<body>

@if(!isset($isPdf) || !$isPdf)
<div class="no-print">
    <button onclick="window.print()" style="padding: 8px 20px; background: #21864B; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
        Print Transport Challan
    </button>
    <a href="{{ route('sales-invoices.challan.pdf', $invoice) }}" style="padding: 8px 18px; background: #fff; color: #24332A; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 500;">
        Download PDF
    </a>
    <a href="{{ route('sales-invoices.show', $invoice) }}" style="padding: 8px 18px; background: #f8f9fa; color: #555; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; font-size: 13px;">
        &larr; Back to Invoice
    </a>
</div>
@endif

<div class="sheet">
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                <div class="company-name">{{ setting('business.name', 'Kisan Enterprises') }}</div>
                <div class="company-tagline">{{ setting('business.tagline', 'Agricultural Crop Trading & Grain Supply') }}</div>
                <div class="company-contact">
                    @if(setting('business.address'))
                        <div>{{ setting('business.address') }}</div>
                    @endif
                    <div>
                        @if(setting('business.phone'))
                            <span>Phone: {{ setting('business.phone') }}</span>
                        @endif
                        @if(setting('business.email'))
                            <span style="margin-left: 10px;">Email: {{ setting('business.email') }}</span>
                        @endif
                    </div>
                    <div>
                        @if(setting('business.gstin'))
                            <strong>GSTIN:</strong> {{ setting('business.gstin') }} &bull;
                        @endif
                        <strong>State:</strong> {{ setting('business.state', 'Uttar Pradesh') }} (Code: 09)
                    </div>
                </div>
            </td>
            <td style="width: 42%; vertical-align: top; text-align: right;">
                <div class="doc-title">DELIVERY CHALLAN</div>
                <div class="doc-subtitle">Rule 55 CGST Rules &bull; Mandi Dispatch Pass</div>
                <div class="doc-meta">
                    <div><strong>Challan No:</strong> <span style="font-size: 13px; color: #155B36;">{{ $invoice->challanNumber() }}</span></div>
                    <div><strong>Challan Date:</strong> {{ fmt_date($invoice->invoice_date) }}</div>
                    <div><strong>Linked Tax Invoice:</strong> {{ $invoice->invoice_number }}</div>
                    @if($invoice->eway_bill_number)
                        <div><strong>E-Way Bill No:</strong> <span style="font-weight: bold; color: #21864B;">{{ $invoice->eway_bill_number }}</span></div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Copy Indicator Bar --}}
    <div class="copies-bar">
        <span>[ &check; ] Original for Transporter / Consignee</span>
        <span>[ ] Duplicate for Dispatch Incharge</span>
        <span>[ ] Triplicate for Accounts</span>
    </div>

    {{-- Vehicle & Logistics Quick Ribbon --}}
    <div class="transport-badge-bar">
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">VEHICLE / TRUCK NO:</span><br>
            <strong style="font-size: 15px; letter-spacing: 0.5px;">{{ $invoice->vehicle_number ?: 'VEHICLE NOT SPECIFIED' }}</strong>
        </div>
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">DRIVER NAME:</span><br>
            <strong>{{ $invoice->driver_name ?: 'Self / Assigned Driver' }}</strong>
        </div>
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">TRANSPORTER / FLEET:</span><br>
            <strong>{{ $invoice->transporter_name ?: 'Direct Mandi Dispatch' }}</strong>
        </div>
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">PURPOSE:</span><br>
            <span style="font-weight: bold; color: #155B36;">Supply for Sale</span>
        </div>
    </div>

    {{-- Consignor and Consignee Side-by-Side --}}
    <table class="info-grid">
        <tr>
            <td class="info-panel" style="width: 50%;">
                <div class="panel-heading">DISPATCH FROM (CONSIGNOR / प्रेषक)</div>
                <div><strong>{{ setting('business.name', 'Kisan Enterprises') }}</strong></div>
                @php
                    $sourceWarehouse = $invoice->items->first()?->warehouse;
                @endphp
                @if($sourceWarehouse)
                    <div><strong>Godown / Yard:</strong> {{ $sourceWarehouse->name }} ({{ $sourceWarehouse->code }})</div>
                    @if($sourceWarehouse->address)
                        <div>{{ $sourceWarehouse->address }}</div>
                    @endif
                @else
                    <div>Krishi Upaj Mandi Premise / Storage Godown</div>
                @endif
                <div><strong>State:</strong> {{ setting('business.state', 'Uttar Pradesh') }}</div>
                @if(setting('business.gstin'))
                    <div><strong>GSTIN:</strong> {{ setting('business.gstin') }}</div>
                @endif
                @if(setting('business.phone'))
                    <div><strong>Phone:</strong> {{ setting('business.phone') }}</div>
                @endif
            </td>
            <td class="info-panel" style="width: 50%;">
                <div class="panel-heading">DELIVER TO (CONSIGNEE / प्राप्तकर्ता)</div>
                <div><strong>{{ $invoice->customer->name }}</strong></div>
                @if($invoice->customer->contact_person)
                    <div>Attn: {{ $invoice->customer->contact_person }}</div>
                @endif
                @if($invoice->delivery_destination)
                    <div><strong>Unloading Destination:</strong> {{ $invoice->delivery_destination }}</div>
                @elseif($invoice->customer->address)
                    <div>{{ $invoice->customer->address }}, {{ $invoice->customer->city }}</div>
                @endif
                <div><strong>State:</strong> {{ $invoice->customer->state ?? setting('business.state', 'Uttar Pradesh') }}</div>
                <div><strong>Phone / Mobile:</strong> {{ $invoice->customer->mobile ?: '-' }}</div>
                <div><strong>GSTIN:</strong> {{ $invoice->customer->gstin ?: 'Unregistered' }}</div>
            </td>
        </tr>
    </table>

    {{-- Commodities Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 35%;">Commodity / Crop Description</th>
                <th style="width: 20%;">Dispatch Godown</th>
                <th style="width: 20%;" class="text-end">Quantity (Quintals)</th>
                <th style="width: 20%;" class="text-end">Quantity (Kilograms)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totQtl = '0.000';
                $totKg = '0.000';
            @endphp
            @foreach($invoice->items as $idx => $item)
                @php
                    $totQtl = bcadd($totQtl, (string) $item->quantity_qtl, 3);
                    $totKg = bcadd($totKg, (string) $item->quantity_kg, 3);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->crop->name }}</strong>
                        @if($item->cropVariety)
                            <span style="color: #666;">- {{ $item->cropVariety->name }}</span>
                        @endif
                        <div style="font-size: 10px; color: #888;">HSN: {{ $item->crop->code ?? '1006' }} &bull; Bulk Agro Commodity</div>
                    </td>
                    <td>{{ $item->warehouse->name }}</td>
                    <td class="text-end fw-bold">{{ bc_round($item->quantity_qtl, 3) }} Qtl</td>
                    <td class="text-end">{{ bc_round($item->quantity_kg, 3) }} Kg</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f2; font-weight: bold;">
                <td colspan="3" class="text-end">TOTAL DISPATCHED WEIGHT:</td>
                <td class="text-end" style="color: #155B36; font-size: 13px;">{{ bc_round($totQtl, 3) }} Qtl</td>
                <td class="text-end" style="color: #155B36; font-size: 13px;">{{ bc_round($totKg, 3) }} Kg</td>
            </tr>
        </tfoot>
    </table>

    {{-- Instructions Box --}}
    <div class="instructions-box">
        <strong>Transport Instructions & Verification:</strong>
        <ol style="margin: 4px 0 0 16px; padding: 0;">
            <li>This delivery challan is issued under Rule 55 of CGST Rules 2017 for transport of goods for supply.</li>
            <li>Weighment done on certified computerized weighbridge before truck departure.</li>
            <li>Driver must verify vehicle tarping, moisture safety, and obtain consignee signature upon delivery.</li>
        </ol>
    </div>

    {{-- Proof of Delivery / Buyer Confirmation Stamp --}}
    @if($invoice->isDelivered())
        <div style="margin-top: 12px; background: #e8f5e9; border: 1.5px solid #2e7d32; border-radius: 5px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-weight: bold; color: #1b5e20; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    &#10004; {{ __('sales.invoices.proof_of_delivery') }} &bull; {{ __('sales.invoices.status_delivered') }} (माल सुरक्षित प्राप्त हुआ)
                </div>
                <div style="font-size: 11px; color: #2e7d32; margin-top: 3px;">
                    <span>{{ __('sales.invoices.delivered_at') }}: <strong>{{ fmt_datetime($invoice->delivered_at) }}</strong></span>
                    @if($invoice->received_by)
                        <span style="margin-left: 10px;">&bull; {{ __('sales.invoices.received_by') }}: <strong>{{ $invoice->received_by }}</strong></span>
                    @endif
                    @if($invoice->deliverer)
                        <span style="margin-left: 10px;">&bull; {{ __('sales.invoices.delivered_by') }}: <strong>{{ $invoice->deliverer->name }}</strong></span>
                    @endif
                </div>
                @if($invoice->delivery_notes)
                    <div style="font-size: 10.5px; color: #388e3c; margin-top: 3px; font-style: italic;">
                        <strong>{{ __('sales.invoices.delivery_notes') }}:</strong> {{ $invoice->delivery_notes }}
                    </div>
                @endif
            </div>
            <div style="border: 2px dashed #2e7d32; border-radius: 4px; padding: 4px 10px; color: #1b5e20; font-weight: bold; font-size: 11px; text-align: center; text-transform: uppercase;">
                DELIVERY CONFIRMED<br><span style="font-size: 9px; font-weight: normal;">सत्यापित पावती</span>
            </div>
        </div>
    @endif

    {{-- Signatures --}}
    <table class="signatures">
        <tr>
            <td class="sig-box" style="width: 25%;">
                <div class="sig-line"></div>
                <div class="sig-label">Driver's Signature (चालक)</div>
                <div class="sig-sub">Received goods in sound condition</div>
            </td>
            <td class="sig-box" style="width: 25%;">
                <div class="sig-line"></div>
                <div class="sig-label">Weighbridge / Gate Incharge</div>
                <div class="sig-sub">Verified Gross & Tare Weight</div>
            </td>
            <td class="sig-box" style="width: 25%;">
                <div class="sig-line"></div>
                <div class="sig-label">Consignee / Mill Receiver</div>
                <div class="sig-sub">
                    @if($invoice->isDelivered())
                        <span style="color: #1b5e20; font-weight: bold;">&#10004; {{ $invoice->received_by ?? 'Received' }}</span>
                    @else
                        Buyer Seal & Signature
                    @endif
                </div>
            </td>
            <td class="sig-box" style="width: 25%;">
                <div class="sig-line"></div>
                <div class="sig-label">For {{ setting('business.name', 'Kisan Enterprises') }}</div>
                <div class="sig-sub">Authorized Dispatch Signatory</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer-note">
        {{ setting('business.name', 'Kisan Enterprises') }} &bull; {{ setting('business.tagline', 'Agricultural Crop Trading') }} &bull; Computer Generated Transport Challan
    </div>
</div>

</body>
</html>

