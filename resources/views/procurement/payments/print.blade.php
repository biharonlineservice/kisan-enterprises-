<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Voucher — {{ $payment->voucher_number }}</title>
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
        .header {
            border-bottom: 2px solid #21864B;
            padding-bottom: 12px;
            margin-bottom: 20px;
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
        Print Voucher
    </button>
    <a href="{{ route('farmer-payments.show', $payment) }}" style="padding: 8px 18px; color: #738078; text-decoration: none; margin-left: 8px; font-size: 13px;">
        Back to Voucher
    </a>
</div>

<div class="sheet">
    <table style="width: 100%; border-bottom: 2px solid #21864B; padding-bottom: 10px; margin-bottom: 15px;">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-name">{{ setting('business.name', 'Kisan Enterprises') }}</div>
                <div class="company-tagline">{{ setting('business.tagline', 'Agricultural Crop Trading') }}</div>
                <div style="font-size: 11px; color: #555;">
                    @if(setting('business.address')) {{ setting('business.address') }}<br> @endif
                    @if(setting('business.phone')) Phone: {{ setting('business.phone') }} @endif
                </div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="doc-title">Farmer Payment Voucher</div>
                <div class="doc-number">{{ $payment->voucher_number }}</div>
                <div style="font-size: 11px; color: #738078;">Date: {{ fmt_date($payment->payment_date) }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <tbody>
            <tr>
                <th>Paid To (Farmer)</th>
                <td>
                    <strong>{{ $payment->farmer->name }}</strong> (ID: {{ $payment->farmer->farmer_id }})<br>
                    <small style="color: #666;">Village: {{ $payment->farmer->village ?? '-' }} | Mobile: {{ $payment->farmer->mobile ?? '-' }}</small>
                </td>
            </tr>
            <tr>
                <th>Against Purchase</th>
                <td>
                    Purchase Ref: <strong>{{ $payment->purchase->purchase_number }}</strong> (Date: {{ fmt_date($payment->purchase->purchase_date) }})<br>
                    Crop: {{ $payment->purchase->crop->name }} | Net Qtl: {{ bc_round($payment->purchase->net_weight_qtl, 3) }} Qtl
                </td>
            </tr>
            <tr>
                <th>{{ bccomp((string) $payment->bonus_amount, '0.00', 2) > 0 ? 'Principal Amount (Purchase Balance)' : 'Amount Disbursed' }}</th>
                <td style="font-size: 14px; font-weight: bold; color: #155B36;">
                    {{ money($payment->amount) }}
                </td>
            </tr>
            @if(bccomp((string) $payment->bonus_amount, '0.00', 2) > 0)
            <tr style="background: #f0f9f3;">
                <th style="background: #e6f4eb; color: #155B36;">Delayed Payment Bonus</th>
                <td style="font-weight: bold; color: #155B36;">
                    + {{ money($payment->bonus_amount) }}
                    <small style="color: #555; font-weight: normal;">
                        ({{ $payment->days_delayed }} days since purchase
                        @if(bccomp((string) $payment->bonus_percentage, '0.00', 2) > 0)
                            @ {{ bc_round($payment->bonus_percentage, 2) }}% {{ $payment->bonus_calculation_mode ?? 'monthly' }}
                        @endif)
                    </small>
                </td>
            </tr>
            <tr>
                <th style="font-weight: bold;">Total Payout to Farmer</th>
                <td style="font-size: 16px; font-weight: bold; color: #155B36;">
                    {{ money($payment->effective_payout) }}
                </td>
            </tr>
            @endif
            <tr>
                <th>Payment Mode</th>
                <td>{{ $payment->method_label }}</td>
            </tr>
            <tr>
                <th>Transaction / Cheque Reference</th>
                <td>{{ $payment->transaction_reference ?? 'N/A' }}</td>
            </tr>
            @if($payment->notes)
            <tr>
                <th>Remarks / Notes</th>
                <td>{{ $payment->notes }}</td>
            </tr>
            @endif
            <tr>
                <th>Remaining Balance on Purchase</th>
                <td style="font-weight: bold; color: {{ bccomp($payment->purchase->balance_due, '0', 2) > 0 ? '#b72b2b' : '#155B36' }};">
                    {{ money($payment->purchase->balance_due) }}
                </td>
            </tr>
        </tbody>
    </table>

    @if(setting('documents.show_signature', '1') == '1')
    <table class="signatures">
        <tr>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Farmer's Signature / Thumb Impression<br><small>(Receiver)</small></div>
            </td>
            <td class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">For {{ setting('business.name', 'Kisan Enterprises') }}<br><small>(Cashier / Authorized Signatory)</small></div>
            </td>
        </tr>
    </table>
    @endif

    <div style="margin-top: 25px; text-align: center; font-size: 10px; color: #738078;">
        This is an official payment voucher generated by {{ setting('business.name', 'Kisan Enterprises') }} ERP.
    </div>
</div>

</body>
</html>
