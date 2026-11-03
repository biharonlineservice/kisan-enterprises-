<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('sales.invoices.print') }} — {{ $invoice->invoice_number }}</title>
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
            padding: 30px;
            border: {{ isset($isPdf) && $isPdf ? 'none' : '1px solid #e3e9e4' }};
            border-radius: {{ isset($isPdf) && $isPdf ? '0' : '6px' }};
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #21864B;
            padding-bottom: 12px;
            margin-bottom: 18px;
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
            margin-bottom: 5px;
        }
        .company-contact {
            font-size: 11px;
            color: #555;
            line-height: 1.4;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #21864B;
            text-transform: uppercase;
            text-align: right;
        }
        .doc-meta {
            text-align: right;
            font-size: 12px;
            color: #444;
            line-height: 1.5;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-panel {
            background: #fbfcfb;
            border: 1px solid #e3e9e4;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 12px;
        }
        .panel-heading {
            font-size: 10px;
            font-weight: bold;
            color: #738078;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            border-bottom: 1px dashed #e3e9e4;
            padding-bottom: 4px;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
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
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .signatures {
            margin-top: 45px;
            width: 100%;
        }
        .sig-box {
            text-align: center;
            vertical-align: bottom;
            padding-top: 40px;
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
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px dashed #e3e9e4;
            text-align: center;
            font-size: 11px;
            color: #738078;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 7px;
            font-size: 10px;
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
    <a href="{{ route('sales-invoices.pdf', $invoice) }}" style="padding: 8px 18px; background: #fff; color: #24332A; border: 1px solid #ccc; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 500;">
        <i class="bi bi-file-earmark-pdf"></i> {{ __('app.download_pdf') }}
    </a>
    <button type="button" onclick="if(navigator.share){navigator.share({title:'{{ $invoice->invoice_number }}',text:'Sales Invoice {{ $invoice->invoice_number }}',url:window.location.href}).catch(()=>{});}else{window.location.href='{{ route('sales-invoices.pdf', $invoice) }}';}" style="padding: 8px 18px; background: #2F7FB8; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
        <i class="bi bi-share"></i> {{ __('app.share') }}
    </button>
    <a href="{{ route('sales-invoices.show', $invoice) }}" style="padding: 8px 18px; color: #738078; text-decoration: none; font-size: 13px;">
        {{ __('app.back') }}
    </a>
</div>
@endif

<div class="sheet">
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                <div class="company-name">{{ setting('business.name', 'Kisan Enterprises') }}</div>
                <div class="company-tagline">{{ setting('business.tagline', __('app.tagline')) }}</div>
                <div class="company-contact">
                    @if(setting('business.address')) {{ setting('business.address') }}<br> @endif
                    @if(setting('business.phone')) Phone: {{ setting('business.phone') }} @endif
                    @if(setting('business.email')) | Email: {{ setting('business.email') }} @endif<br>
                    @if(setting('business.gstin')) <strong>GSTIN:</strong> {{ setting('business.gstin') }} @endif
                    @if(setting('business.pan')) | <strong>PAN:</strong> {{ setting('business.pan') }} @endif
                </div>
            </td>
            <td style="vertical-align: top; width: 40%; text-align: right;">
                <div class="doc-title">{{ setting('tax.enabled', '0') == '1' ? 'Tax Invoice' : (app()->getLocale() === 'hi' ? 'बिक्री बिल (इनवॉइस)' : 'Sales Invoice') }}</div>
                <div class="doc-meta" style="margin-top: 5px;">
                    <strong>{{ __('sales.invoices.invoice_number') }}:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>{{ __('app.date') }}:</strong> {{ fmt_date($invoice->invoice_date) }}<br>
                    @if($invoice->due_date)
                        <strong>{{ __('sales.orders.delivery_date') }}:</strong> {{ fmt_date($invoice->due_date) }}<br>
                    @endif
                    <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- Buyer & Transport Grid --}}
    <table class="info-grid">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                <div class="info-panel">
                    <div class="panel-heading">Billed & Shipped To</div>
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    Type: {{ $invoice->customer->customer_type_label }}<br>
                    Address: {{ $invoice->customer->address ?? 'N/A' }}<br>
                    City/State: {{ $invoice->customer->city ?? '-' }}, {{ $invoice->customer->state ?? '-' }}<br>
                    Phone: {{ $invoice->customer->phone ?? 'N/A' }}<br>
                    GSTIN: <strong>{{ $invoice->customer->gstin ?? 'Unregistered' }}</strong>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                <div class="info-panel">
                    <div class="panel-heading">Transport & Dispatch Particulars</div>
                    Vehicle No: <strong>{{ $invoice->vehicle_number ?? 'Direct Handover' }}</strong><br>
                    Driver Name: {{ $invoice->driver_name ?? 'N/A' }}<br>
                    Transporter: {{ $invoice->transporter_name ?? 'Direct' }}<br>
                    E-Way Bill: {{ $invoice->eway_bill_number ?? 'N/A' }}<br>
                    Destination: {{ $invoice->delivery_destination ?? $invoice->customer->city ?? 'As Per Order' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Line Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Crop Particulars</th>
                <th style="width: 18%;">Origin Godown</th>
                <th class="text-end" style="width: 14%;">Quantity (Qtl)</th>
                <th class="text-end" style="width: 14%;">Rate / Qtl</th>
                <th class="text-end" style="width: 14%;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->crop->name }}</strong>
                        @if($item->cropVariety)
                            <br><small style="color: #666;">Variety: {{ $item->cropVariety->name }}</small>
                        @endif
                    </td>
                    <td>{{ $item->warehouse->name }}</td>
                    <td class="text-end" style="font-weight: bold;">{{ bc_round($item->quantity_qtl, 3) }}</td>
                    <td class="text-end">{{ money($item->rate_per_qtl) }}</td>
                    <td class="text-end" style="font-weight: bold;">{{ money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Financial Summary & Bank Remittance --}}
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 15px;">
                @if($invoice->remarks)
                    <div style="font-size: 11px; color: #555; background: #fafafa; border: 1px solid #e3e9e4; padding: 10px; border-radius: 4px; margin-bottom: 10px;">
                        <strong>Remarks:</strong> {{ $invoice->remarks }}
                    </div>
                @endif

                @if($invoice->receipts->count() > 0)
                    <div style="font-size: 11px; margin-bottom: 10px;">
                        <strong>Receipts Recorded:</strong>
                        <ul style="margin: 4px 0 0 18px; padding: 0;">
                            @foreach($invoice->receipts as $r)
                                <li>{{ fmt_date($r->receipt_date) }}: {{ money($r->amount) }} via {{ $r->method_label }} (Ref: {{ $r->transaction_reference ?? $r->receipt_number }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td style="padding: 4px 0;">Subtotal:</td>
                        <td style="padding: 4px 0; text-align: right; font-weight: bold;">{{ money($invoice->subtotal_amount) }}</td>
                    </tr>
                    @if(bccomp($invoice->discount_amount, '0', 2) > 0)
                        <tr>
                            <td style="padding: 4px 0; color: #b72b2b;">Discount:</td>
                            <td style="padding: 4px 0; text-align: right; color: #b72b2b;">- {{ money($invoice->discount_amount) }}</td>
                        </tr>
                    @endif
                    @if(bccomp($invoice->tax_amount, '0', 2) > 0)
                        <tr>
                            <td style="padding: 4px 0;">Tax / GST:</td>
                            <td style="padding: 4px 0; text-align: right;">+ {{ money($invoice->tax_amount) }}</td>
                        </tr>
                    @endif
                    @if(bccomp($invoice->round_off, '0', 2) != 0)
                        <tr>
                            <td style="padding: 4px 0; color: #777;">Round Off:</td>
                            <td style="padding: 4px 0; text-align: right; color: #777;">{{ $invoice->round_off }}</td>
                        </tr>
                    @endif
                    <tr style="border-top: 1px solid #24332A; border-bottom: 1px solid #24332A;">
                        <td style="padding: 6px 0; font-weight: bold; font-size: 14px;">Total Amount:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold; font-size: 14px; color: #155B36;">
                            {{ money($invoice->total_amount) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">Amount Received:</td>
                        <td style="padding: 4px 0; text-align: right; color: #155B36;">{{ money($invoice->paid_amount) }}</td>
                    </tr>
                    <tr style="border-top: 1px dashed #e3e9e4;">
                        <td style="padding: 6px 0; font-weight: bold;">Balance Due:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold; color: {{ bccomp($invoice->balance_due, '0', 2) > 0 ? '#b72b2b' : '#155B36' }};">
                            {{ money($invoice->balance_due) }}
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
            <td class="sig-box" style="width: 50%;">
                <div class="sig-line"></div>
                <div class="sig-label">Receiver's Signature / Seal</div>
            </td>
            <td class="sig-box" style="width: 50%;">
                <div class="sig-line"></div>
                <div class="sig-label">For {{ setting('business.name', 'Kisan Enterprises') }}<br><small>(Authorized Signatory)</small></div>
            </td>
        </tr>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer-note">
        {{ setting('documents.invoice_footer', 'This is a computer generated document.') }}<br>
        <small>Generated on {{ now()->format('d-m-Y H:i:s') }} | Ref: {{ $invoice->invoice_number }}</small>
    </div>
</div>

</body>
</html>
