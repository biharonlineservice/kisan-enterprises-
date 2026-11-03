<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>Stock Transfer Delivery Challan — {{ $transfer->challanNumber() }}</title>
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
            padding: 8px 10px;
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
            background: #fef8eb;
            border: 1px solid #fde7b3;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 10px;
            font-size: 11px;
            color: #855302;
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
        Print Transfer Challan
    </button>
    <a href="{{ route('stock-transfers.challan.pdf', $transfer) }}" style="padding: 8px 18px; background: #fff; color: #24332A; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 500;">
        Download PDF
    </a>
    <a href="{{ route('stock-transfers.show', $transfer) }}" style="padding: 8px 18px; background: #f8f9fa; color: #555; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; font-size: 13px;">
        &larr; Back to Transfer
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
                <div style="font-size: 11px; color: #555;">
                    <div>{{ setting('business.address', 'Krishi Upaj Mandi Premise') }}</div>
                    <div>Phone: {{ setting('business.phone', '-') }} &bull; State: {{ setting('business.state', 'Uttar Pradesh') }}</div>
                </div>
            </td>
            <td style="width: 42%; vertical-align: top; text-align: right;">
                <div class="doc-title">STOCK TRANSFER CHALLAN</div>
                <div class="doc-subtitle">Inter-Warehouse Movement &bull; Not for Sale</div>
                <div class="doc-meta">
                    <div><strong>Challan No:</strong> <span style="font-size: 13px; color: #155B36;">{{ $transfer->challanNumber() }}</span></div>
                    <div><strong>Transfer Date:</strong> {{ fmt_date($transfer->transfer_date) }}</div>
                    <div><strong>Reference No:</strong> {{ $transfer->transfer_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Vehicle Quick Ribbon --}}
    <div class="transport-badge-bar">
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">VEHICLE / TRACTOR NO:</span><br>
            <strong style="font-size: 15px;">{{ $transfer->vehicle_number ?: 'Trolley / Internal Movement' }}</strong>
        </div>
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">RECORDED BY:</span><br>
            <strong>{{ $transfer->creator?->name ?? 'Store Incharge' }}</strong>
        </div>
        <div class="transport-item">
            <span style="color: #666; font-size: 11px;">PURPOSE OF MOVEMENT:</span><br>
            <span style="font-weight: bold; color: #155B36;">Inter-Branch Stock Relocation</span>
        </div>
    </div>

    {{-- From and To Warehouses --}}
    <table class="info-grid">
        <tr>
            <td class="info-panel" style="width: 50%;">
                <div class="panel-heading">SOURCE GODOWN (DISPATCH FROM)</div>
                <div style="font-size: 13px; font-weight: bold; color: #24332A;">{{ $transfer->fromWarehouse->name }}</div>
                <div>Code: <strong>{{ $transfer->fromWarehouse->code }}</strong></div>
                @if($transfer->fromWarehouse->address)
                    <div>Address: {{ $transfer->fromWarehouse->address }}</div>
                @endif
                <div>Supervisor / Incharge: {{ $transfer->fromWarehouse->supervisor?->name ?? 'Godown Incharge' }}</div>
            </td>
            <td class="info-panel" style="width: 50%;">
                <div class="panel-heading">DESTINATION GODOWN (RECEIVE AT)</div>
                <div style="font-size: 13px; font-weight: bold; color: #155B36;">{{ $transfer->toWarehouse->name }}</div>
                <div>Code: <strong>{{ $transfer->toWarehouse->code }}</strong></div>
                @if($transfer->toWarehouse->address)
                    <div>Address: {{ $transfer->toWarehouse->address }}</div>
                @endif
                <div>Supervisor / Incharge: {{ $transfer->toWarehouse->supervisor?->name ?? 'Godown Incharge' }}</div>
            </td>
        </tr>
    </table>

    {{-- Commodity Particulars --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">#</th>
                <th style="width: 42%;">Agricultural Commodity / Crop</th>
                <th style="width: 25%;" class="text-end">Transferred Weight (Qtl)</th>
                <th style="width: 25%;" class="text-end">Transferred Weight (Kg)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <strong>{{ $transfer->crop->name }}</strong>
                    @if($transfer->cropVariety)
                        <span style="color: #666;">({{ $transfer->cropVariety->name }})</span>
                    @endif
                    <div style="font-size: 10px; color: #888;">Bulk Stock In-Transit</div>
                </td>
                <td class="text-end" style="font-size: 14px; font-weight: bold; color: #155B36;">{{ bc_round($transfer->quantity_qtl, 3) }} Qtl</td>
                <td class="text-end" style="font-size: 14px;">{{ bc_round($transfer->quantity_kg, 3) }} Kg</td>
            </tr>
        </tbody>
    </table>

    @if($transfer->notes)
        <div style="margin: 8px 0; font-size: 11px; color: #555;">
            <strong>Remarks / Notes:</strong> {{ $transfer->notes }}
        </div>
    @endif

    <div class="instructions-box">
        <strong>Mandatory Note:</strong> This document serves as an internal stock transfer delivery challan. This movement does not constitute a sale. Goods remain the property of {{ setting('business.name', 'Kisan Enterprises') }}. Receiving godown must inspect sacks and log receipt on weighbridge.
    </div>

    {{-- Signatures --}}
    <table class="signatures">
        <tr>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Dispatched By (Source Incharge)</div>
                <div class="sig-sub">{{ $transfer->fromWarehouse->name }}</div>
            </td>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Driver / Transporter Signature</div>
                <div class="sig-sub">Vehicle: {{ $transfer->vehicle_number ?: 'Trolley' }}</div>
            </td>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Received By (Destination Incharge)</div>
                <div class="sig-sub">{{ $transfer->toWarehouse->name }}</div>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer-note">
        {{ setting('business.name', 'Kisan Enterprises') }} &bull; Internal Logistics & Warehouse Management System
    </div>
</div>

</body>
</html>

