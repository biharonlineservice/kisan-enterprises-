<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('procurement.purchases.receipt') }} — {{ $purchase->purchase_number }}</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: {{ isset($isPdf) && $isPdf ? 'none' : '1px solid #e3e9e4' }};
            border-radius: {{ isset($isPdf) && $isPdf ? '0' : '6px' }};
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #21864B;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #155B36;
            margin-bottom: 4px;
        }
        .company-tagline {
            font-size: 12px;
            color: #738078;
            margin-bottom: 6px;
        }
        .company-contact {
            font-size: 11px;
            color: #555;
            line-height: 1.5;
        }
        .doc-title-block {
            text-align: right;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            color: #21864B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 14px;
            font-weight: bold;
            color: #24332A;
            margin-top: 3px;
        }
        .doc-date {
            font-size: 11px;
            color: #738078;
        }
        .grid-2 {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .col-half {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .panel {
            background: #fbfcfb;
            border: 1px solid #e3e9e4;
            border-radius: 4px;
            padding: 12px;
            margin-right: 10px;
        }
        .col-half:last-child .panel {
            margin-right: 0;
            margin-left: 10px;
        }
        .panel-title {
            font-size: 11px;
            font-weight: bold;
            color: #738078;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-bottom: 1px dashed #e3e9e4;
            padding-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #e3e9e4;
            padding: 8px 10px;
            font-size: 12px;
        }
        table.data-table th {
            background-color: #f1f5f2;
            color: #24332A;
            font-weight: bold;
            text-align: left;
        }
        table.data-table td.text-end, table.data-table th.text-end {
            text-align: right;
        }
        table.data-table td.text-center, table.data-table th.text-center {
            text-align: center;
        }
        .financial-summary {
            width: 100%;
            margin-bottom: 25px;
        }
        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .sig-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 45px;
        }
        .sig-line {
            border-top: 1px solid #24332A;
            width: 70%;
            margin: 0 auto 5px auto;
        }
        .sig-label {
            font-size: 11px;
            color: #555;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px dashed #e3e9e4;
            text-align: center;
            font-size: 11px;
            color: #738078;
        }
        .status-stamp {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .status-finalized { background: #eaf5ed; color: #155B36; border: 1px solid #c2e2cc; }
        .status-draft { background: #fef8eb; color: #b78103; border: 1px solid #fde7b3; }
        .status-reversed { background: #faecec; color: #b72b2b; border: 1px solid #f2bcbc; }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; padding: 0; }
            .sheet { border: none; padding: 15px; }
        }
    </style>
</head>
<body>

@if(!isset($isPdf) || !$isPdf)
<div class="no-print" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
    <button onclick="window.print()" style="padding: 8px 18px; background: #21864B; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
        <i class="bi bi-printer"></i> {{ __('app.print') }}
    </button>
    <a href="{{ route('purchases.pdf', $purchase) }}" style="padding: 8px 18px; background: #fff; color: #24332A; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 500;">
        <i class="bi bi-file-earmark-pdf"></i> {{ __('app.download_pdf') }}
    </a>
    <button type="button" onclick="if(navigator.share){navigator.share({title:'{{ $purchase->purchase_number }}',text:'Purchase Receipt {{ $purchase->purchase_number }}',url:window.location.href}).catch(()=>{});}else{window.location.href='{{ route('purchases.pdf', $purchase) }}';}" style="padding: 8px 18px; background: #2F7FB8; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
        <i class="bi bi-share"></i> {{ __('app.share') }}
    </button>
    <a href="{{ route('purchases.show', $purchase) }}" style="padding: 8px 18px; color: #738078; text-decoration: none; font-size: 13px;">
        {{ __('app.back') }}
    </a>
</div>
@endif

<div class="sheet">
    {{-- Header --}}
    <table style="width: 100%; border-bottom: 2px solid #21864B; padding-bottom: 10px; margin-bottom: 15px;">
        <tr>
            <td style="vertical-align: top;">
                @php $receiptLogo = setting('business.logo'); @endphp
                @if($receiptLogo)
                    <div style="margin-bottom: 6px;">
                        <img src="{{ isset($isPdf) && $isPdf ? public_path('uploads/' . $receiptLogo) : asset('uploads/' . $receiptLogo) }}" alt="{{ setting('business.name') }}" style="max-height: 44px; max-width: 200px; object-fit: contain;">
                    </div>
                @endif
                <div class="company-name">{{ setting('business.name', 'Kisan Enterprises') }}</div>
                <div class="company-tagline">{{ setting('business.tagline', __('app.tagline')) }}</div>
                <div class="company-contact">
                    @if(setting('business.address')) {{ setting('business.address') }}<br> @endif
                    @if(setting('business.phone')) Phone: {{ setting('business.phone') }} @endif
                    @if(setting('business.gstin')) | GSTIN: {{ setting('business.gstin') }} @endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="doc-title">{{ app()->getLocale() === 'hi' ? 'तौल पर्ची (खरीद रसीद)' : 'Purchase Weighment Slip' }}</div>
                <div class="doc-number">{{ $purchase->purchase_number }}</div>
                <div class="doc-date">{{ __('app.date') }}: {{ fmt_date($purchase->purchase_date) }}</div>
                <div style="margin-top: 5px;">
                    <span class="status-stamp status-{{ $purchase->status }}">{{ ucfirst($purchase->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Farmer & Warehouse Info --}}
    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                <div class="panel">
                    <div class="panel-title">Farmer Details</div>
                    <strong>{{ $purchase->farmer->name }}</strong><br>
                    ID: {{ $purchase->farmer->farmer_id }}<br>
                    Village: {{ $purchase->farmer->village ?? 'N/A' }}<br>
                    Phone: {{ $purchase->farmer->mobile ?? 'N/A' }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                <div class="panel">
                    <div class="panel-title">Procurement Location & Transport</div>
                    Warehouse: <strong>{{ $purchase->warehouse->name }}</strong><br>
                    Vehicle No: {{ $purchase->vehicle_number ?? 'Direct delivery' }}<br>
                    Driver: {{ $purchase->driver_name ?? 'N/A' }}<br>
                    Grade: {{ $purchase->quality_grade ?? 'Standard' }} (Moisture: {{ $purchase->moisture_pct ? bc_round($purchase->moisture_pct, 1) . '%' : 'N/A' }})
                </div>
            </td>
        </tr>
    </table>

    {{-- Weighment Particulars Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>Crop Particulars</th>
                <th class="text-end">Gross (Kg)</th>
                <th class="text-end">Tare (Kg)</th>
                <th class="text-end">Net (Kg)</th>
                <th class="text-end">Net Qtl</th>
                <th class="text-end">Rate / Qtl</th>
                <th class="text-end">Base Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $purchase->crop->name }}</strong>
                    @if($purchase->cropVariety)
                        <br><small style="color: #666;">Variety: {{ $purchase->cropVariety->name }}</small>
                    @endif
                </td>
                <td class="text-end">{{ bc_round($purchase->gross_weight_kg, 3) }}</td>
                <td class="text-end">{{ bc_round($purchase->tare_weight_kg, 3) }}</td>
                <td class="text-end font-weight-bold">{{ bc_round($purchase->net_weight_kg, 3) }}</td>
                <td class="text-end" style="font-weight: bold; color: #155B36;">{{ bc_round($purchase->net_weight_qtl, 3) }}</td>
                <td class="text-end">{{ money($purchase->rate_per_qtl) }}</td>
                <td class="text-end" style="font-weight: bold;">{{ money($purchase->base_amount) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Settlement Breakdown Table --}}
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 15px;">
                @if($purchase->remarks)
                    <div style="font-size: 11px; color: #555; background: #fafafa; border: 1px solid #e3e9e4; padding: 10px; border-radius: 4px;">
                        <strong>Remarks:</strong> {{ $purchase->remarks }}
                    </div>
                @endif
                @if($purchase->payments->count() > 0)
                    <div style="margin-top: 10px; font-size: 11px;">
                        <strong>Payments Made:</strong>
                        <ul style="margin: 4px 0 0 18px; padding: 0;">
                            @foreach($purchase->payments as $pmt)
                                <li>{{ fmt_date($pmt->payment_date) }}: {{ money($pmt->amount) }} via {{ $pmt->method_label }} ({{ $pmt->voucher_number }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <tr>
                        <td style="padding: 4px 0;">Base Amount:</td>
                        <td style="padding: 4px 0; text-align: right; font-weight: bold;">{{ money($purchase->base_amount) }}</td>
                    </tr>
                    @if(bccomp($purchase->deductions_amount, '0', 2) > 0)
                    <tr>
                        <td style="padding: 4px 0; color: #b72b2b;">Deductions:</td>
                        <td style="padding: 4px 0; text-align: right; color: #b72b2b;">- {{ money($purchase->deductions_amount) }}</td>
                    </tr>
                    @endif
                    @if(bccomp($purchase->additions_amount, '0', 2) > 0)
                    <tr>
                        <td style="padding: 4px 0; color: #155B36;">Additions:</td>
                        <td style="padding: 4px 0; text-align: right; color: #155B36;">+ {{ money($purchase->additions_amount) }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid #24332A; border-bottom: 1px solid #24332A;">
                        <td style="padding: 6px 0; font-weight: bold; font-size: 13px;">Net Payable:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold; font-size: 13px; color: #155B36;">
                            {{ money($purchase->net_payable_amount) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">Paid Amount:</td>
                        <td style="padding: 4px 0; text-align: right;">{{ money($purchase->paid_amount) }}</td>
                    </tr>
                    <tr style="border-top: 1px dashed #e3e9e4;">
                        <td style="padding: 6px 0; font-weight: bold;">Balance Due:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold; color: {{ bccomp($purchase->balance_due, '0', 2) > 0 ? '#b72b2b' : '#155B36' }};">
                            {{ money($purchase->balance_due) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Signatures --}}
    @if(setting('documents.show_signature', '1') == '1')
    <table class="signatures">
        <tr>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Farmer's Signature / Thumb Impression</div>
            </td>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">For {{ setting('business.name', 'Kisan Enterprises') }}<br><small>(Authorized Signatory)</small></div>
            </td>
        </tr>
    </table>
    @endif

    {{-- Footer Note --}}
    <div class="footer-note">
        {{ setting('documents.receipt_footer', 'Please verify weight and amount before signing.') }}<br>
        <small>Generated on {{ now()->format('d-m-Y H:i:s') }} | Ref: {{ $purchase->purchase_number }}</small>
    </div>
</div>

</body>
</html>
