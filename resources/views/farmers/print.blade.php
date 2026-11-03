<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Profile — {{ $farmer->farmer_id }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #fff; color: #000; font-family: "Segoe UI", Arial, sans-serif; font-size: 14px; }
        .print-card { max-width: 800px; margin: 20px auto; padding: 25px; border: 1px solid #ddd; border-radius: 8px; }
        .header-logo { max-height: 50px; }
        @media print {
            .no-print { display: none !important; }
            .print-card { border: 0; padding: 0; margin: 0; max-width: 100%; }
            body { font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center py-3 bg-light border-bottom">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">Print Farmer Card</button>
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
                <span class="badge bg-dark fs-6 px-3 py-2">FARMER REGISTRATION CARD</span>
                <div class="fw-bold fs-5 mt-1 text-primary">{{ $farmer->farmer_id }}</div>
            </div>
        </div>

        {{-- Farmer Details --}}
        <div class="row g-3 mb-4">
            <div class="col-8">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th style="width: 35%;">Farmer Name:</th>
                        <td class="fw-bold fs-5">{{ $farmer->name }}</td>
                    </tr>
                    <tr>
                        <th>Mobile Number:</th>
                        <td>{{ $farmer->mobile ?: 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <th>Village:</th>
                        <td class="fw-semibold">{{ $farmer->village ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Gram Panchayat:</th>
                        <td>{{ $farmer->panchayat ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Block / District:</th>
                        <td>{{ $farmer->block ?: '—' }}, {{ $farmer->district ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td>{{ $farmer->state ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Street Address:</th>
                        <td>{{ $farmer->address ?: '—' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-4">
                <div class="border rounded p-3 text-center bg-light">
                    <small class="text-muted d-block mb-1">Registration Date</small>
                    <div class="fw-bold mb-3">{{ fmt_date($farmer->registration_date) }}</div>
                    <small class="text-muted d-block mb-1">Account Status</small>
                    <span class="badge bg-{{ $farmer->is_active ? 'success' : 'danger' }} px-3 py-1">
                        {{ $farmer->is_active ? 'ACTIVE GROWER' : 'INACTIVE' }}
                    </span>
                    <small class="text-muted d-block mt-3 mb-1">Initial Opening Balance</small>
                    <div class="fw-bold text-dark">{{ money($farmer->opening_balance) }}</div>
                </div>
            </div>
        </div>

        @if($farmer->bank_name || $farmer->bank_account_number)
            <div class="border rounded p-3 mb-4 bg-light">
                <h6 class="fw-bold text-uppercase small text-muted mb-2">Settlement Bank Account Details</h6>
                <div class="row g-2 small">
                    <div class="col-6"><strong>Bank Name:</strong> {{ $farmer->bank_name ?: '—' }}</div>
                    <div class="col-6"><strong>Branch:</strong> {{ $farmer->bank_branch ?: '—' }}</div>
                    <div class="col-6"><strong>Account Number:</strong> {{ $farmer->bank_account_number ?: '—' }}</div>
                    <div class="col-6"><strong>IFSC Code:</strong> {{ $farmer->bank_ifsc ?: '—' }}</div>
                    @if($farmer->id_type)
                        <div class="col-12 mt-1"><strong>Govt ID:</strong> {{ $farmer->id_type }} — {{ $farmer->id_number }}</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="mt-5 pt-4 border-top d-flex justify-content-between">
            <div class="text-center" style="width: 220px;">
                <div class="border-top border-dark pt-1 small">Farmer Signature / Thumb Impression</div>
            </div>
            <div class="text-center" style="width: 220px;">
                <div class="border-top border-dark pt-1 small">Authorized Signatory<br>{{ setting('business.name') }}</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top" style="font-size: 11px;">
            This document is issued by {{ setting('business.name') }} for registered agricultural grain procurement &bull; Printed on {{ date('d-m-Y h:i A') }}
        </div>
    </div>
</body>
</html>
