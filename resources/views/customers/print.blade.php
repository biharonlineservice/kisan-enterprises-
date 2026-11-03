<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Profile — {{ $customer->customer_id }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #fff; color: #000; font-family: "Segoe UI", Arial, sans-serif; font-size: 14px; }
        .print-card { max-width: 800px; margin: 20px auto; padding: 25px; border: 1px solid #ddd; border-radius: 8px; }
        @media print {
            .no-print { display: none !important; }
            .print-card { border: 0; padding: 0; margin: 0; max-width: 100%; }
            body { font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center py-3 bg-light border-bottom">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">Print Customer Profile</button>
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
                <span class="badge bg-dark fs-6 px-3 py-2">BUYER & MILL PROFILE</span>
                <div class="fw-bold fs-5 mt-1 text-primary">{{ $customer->customer_id }}</div>
            </div>
        </div>

        {{-- Details --}}
        <div class="row g-3 mb-4">
            <div class="col-8">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th style="width: 35%;">Business / Mill Name:</th>
                        <td class="fw-bold fs-5">{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <th>Category:</th>
                        <td><span class="badge bg-light text-dark border">{{ $customer->type_name }}</span></td>
                    </tr>
                    <tr>
                        <th>Contact Person:</th>
                        <td>{{ $customer->contact_person ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Mobile / Phone:</th>
                        <td>{{ $customer->mobile ?: '—' }} @if($customer->alt_phone) / {{ $customer->alt_phone }} @endif</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $customer->email ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>GSTIN:</th>
                        <td class="font-monospace fw-bold">{{ $customer->gstin ?: 'Not Registered / Unprovided' }}</td>
                    </tr>
                    <tr>
                        <th>PAN:</th>
                        <td class="font-monospace">{{ $customer->pan ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th>Premises Address:</th>
                        <td>{{ $customer->address ?: '—' }}, {{ $customer->city ?: '—' }}, {{ $customer->state ?: '—' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-4">
                <div class="border rounded p-3 text-center bg-light">
                    <small class="text-muted d-block mb-1">Account Status</small>
                    <span class="badge bg-{{ $customer->is_active ? 'success' : 'danger' }} px-3 py-1 mb-3">
                        {{ $customer->is_active ? 'ACTIVE BUYER' : 'INACTIVE' }}
                    </span>
                    <small class="text-muted d-block mb-1">Approved Credit Limit</small>
                    <div class="fw-bold text-dark fs-5 mb-2">{{ money($customer->credit_limit) }}</div>
                    <small class="text-muted d-block mb-1">Payment Credit Terms</small>
                    <div class="fw-semibold mb-2">{{ $customer->payment_terms_days }} Days</div>
                    <small class="text-muted d-block mb-1">Opening Balance</small>
                    <div class="fw-semibold mb-2 {{ $customer->opening_balance > 0 ? 'text-primary' : ($customer->opening_balance < 0 ? 'text-danger' : 'text-muted') }}">
                        {{ money($customer->opening_balance) }}
                    </div>
                    <small class="text-muted d-block mb-1">Current Balance Due</small>
                    <div class="fw-bold fs-5 {{ $customer->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ money($customer->current_balance) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-top d-flex justify-content-between">
            <div class="text-center" style="width: 220px;">
                <div class="border-top border-dark pt-1 small">Customer Signature & Stamp</div>
            </div>
            <div class="text-center" style="width: 220px;">
                <div class="border-top border-dark pt-1 small">Authorized Signatory<br>{{ setting('business.name') }}</div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top" style="font-size: 11px;">
            Issued by {{ setting('business.name') }} &bull; Printed on {{ date('d-m-Y h:i A') }}
        </div>
    </div>
</body>
</html>
