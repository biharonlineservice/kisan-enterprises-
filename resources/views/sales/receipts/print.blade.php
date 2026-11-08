<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Receipt — {{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #24332A;
            font-size: 13px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: #f6f8f6;
        }
        .sheet {
            background: #ffffff;
            max-width: 750px;
            margin: 0 auto;
            padding: 25px 30px;
            border: 1px solid #e3e9e4;
            border-radius: 6px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #155B36;
        }
        .company-tagline {
            font-size: 11px;
            color: #738078;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #21864B;
            text-transform: uppercase;
            text-align: right;
        }
        .doc-number {
            font-size: 13px;
            font-weight: bold;
            text-align: right;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #e3e9e4;
            padding: 8px 12px;
            font-size: 12px;
        }
        table.data-table th {
            background: #f1f5f2;
            text-align: left;
            width: 30%;
        }
        .signatures {
            margin-top: 45px;
            display: table;
            width: 100%;
        }
        .sig-box {
            display: table-cell;
            width: 50%;
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
        .no-print {
            text-align: center;
            margin-bottom: 15px;
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; padding: 0; }
            .sheet { border: none; padding: 10px; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding: 8px 18px; background: #21864B; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
        Print Receipt
    </button>
    <a href="{{ route('customer-receipts.show', $receipt) }}" style="padding: 8px 18px; color: #738078; text-decoration: none; margin-left: 8px; font-size: 13px;">
        Back to Receipt
    </a>
</div>

<div class="sheet">
    <table style="width: 100%; border-bottom: 2px solid #21864B; padding-bottom: 10px; margin-bottom: 15px;">
        <tr>
            <td style="vertical-align: top;">
                @php $rcptLogo = setting('business.logo'); @endphp
                @if($rcptLogo)
                    <div style="margin-bottom: 6px;">
                        <img src="{{ isset($isPdf) && $isPdf ? public_path('uploads/' . $rcptLogo) : asset('uploads/' . $rcptLogo) }}" alt="{{ setting('business.name') }}" style="max-height: 44px; max-width: 200px; object-fit: contain;">
                    </div>
                @endif
                <div class="company-name">{{ setting('business.name', 'Kisan Enterprises') }}</div>
                <div class="company-tagline">{{ setting('business.tagline', 'Agricultural Crop Trading') }}</div>
                <div style="font-size: 11px; color: #555;">
                    @if(setting('business.address')) {{ setting('business.address') }}<br> @endif
                    @if(setting('business.phone')) Phone: {{ setting('business.phone') }} @endif
                    @if(setting('business.gstin')) | GSTIN: {{ setting('business.gstin') }} @endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="doc-title">Customer Receipt Voucher</div>
                <div class="doc-number">{{ $receipt->receipt_number }}</div>
                <div style="font-size: 11px; color: #738078;">Date: {{ fmt_date($receipt->receipt_date) }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <tbody>
            <tr>
                <th>Received From (Customer)</th>
                <td>
                    <strong>{{ $receipt->customer->name }}</strong> ({{ $receipt->customer->customer_type_label }})<br>
                    <small style="color: #666;">GSTIN: {{ $receipt->customer->gstin ?? 'Unregistered' }} | Phone: {{ $receipt->customer->phone ?? '-' }}</small>
                </td>
            </tr>
            <tr>
                <th>Against Sales Invoice</th>
                <td>
                    Invoice Ref: <strong>{{ $receipt->invoice->invoice_number }}</strong> (Date: {{ fmt_date($receipt->invoice->invoice_date) }})<br>
                    Total Invoice Amount: {{ money($receipt->invoice->total_amount) }}
                </td>
            </tr>
            <tr>
                <th>Amount Received</th>
                <td style="font-size: 15px; font-weight: bold; color: #155B36;">
                    {{ money($receipt->amount) }}
                </td>
            </tr>
            <tr>
                <th>Payment Mode</th>
                <td>{{ $receipt->method_label }}</td>
            </tr>
            <tr>
                <th>Transaction Reference</th>
                <td>{{ $receipt->transaction_reference ?? 'N/A' }}</td>
            </tr>
            @if($receipt->bank_name)
            <tr>
                <th>Remitting Bank</th>
                <td>{{ $receipt->bank_name }}</td>
            </tr>
            @endif
            @if($receipt->notes)
            <tr>
                <th>Remarks / Notes</th>
                <td>{{ $receipt->notes }}</td>
            </tr>
            @endif
            <tr>
                <th>Remaining Balance on Invoice</th>
                <td style="font-weight: bold; color: {{ bccomp($receipt->invoice->balance_due, '0', 2) > 0 ? '#b72b2b' : '#155B36' }};">
                    {{ money($receipt->invoice->balance_due) }}
                </td>
            </tr>
        </tbody>
    </table>

    @if(setting('documents.show_signature', '1') == '1')
    <table class="signatures">
        <tr>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Customer / Representative Signature</div>
            </td>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">For {{ setting('business.name', 'Kisan Enterprises') }}<br><small>(Authorized Signatory)</small></div>
            </td>
        </tr>
    </table>
    @endif

    <div style="margin-top: 25px; text-align: center; font-size: 10px; color: #738078;">
        This is an official payment acknowledgement receipt generated by {{ setting('business.name', 'Kisan Enterprises') }} ERP.
    </div>
</div>

</body>
</html>
