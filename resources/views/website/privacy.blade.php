@extends('layouts.website')

@section('title', 'Privacy Policy — Farmer & Trade Partner Data Protection')
@section('meta_description', 'Privacy policy and data governance practices of KisanHub regarding farmer registration, banking details, and commercial trading records.')

@section('content')
<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold text-dark mb-1">Privacy Policy & Data Governance</h1>
        <p class="text-muted mb-0">Last updated: {{ date('F Y') }} &bull; {{ setting('website.brand_name', 'KisanHub') }}</p>
    </div>
</div>

<div class="container py-2 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h4 class="fw-bold text-dark mb-3">1. Commitment to Data Confidentiality</h4>
                    <p class="text-muted leading-relaxed">
                        At <strong>{{ setting('website.brand_name', 'KisanHub') }}</strong> (a digital agri-trading platform powered by <strong>{{ setting('business.name', 'Kisan Enterprises') }}</strong>, "we", "our", or "the Platform"), we recognize that the personal and financial information entrusted to us by farmers, agricultural producers, rice millers, and trade partners is sensitive and critical. This policy details how we collect, handle, store, and safeguard your data across our physical weighbridge operations, ERP system, and public web portals.
                    </p>

                    <h4 class="fw-bold text-dark mt-4 mb-3">2. Information We Collect</h4>
                    <p class="text-muted mb-2">In the ordinary course of agricultural trade and statutory compliance, we collect:</p>
                    <ul class="text-muted mb-3">
                        <li><strong>Farmer Enrollment Data:</strong> Full name, father's/husband's name, mobile number, village, post office, tehsil, district, state, and pincode.</li>
                        <li><strong>Financial & Payment Credentials:</strong> Bank account number, IFSC code, and bank branch name—collected exclusively for remitting crop sale proceeds via RTGS/NEFT/IMPS.</li>
                        <li><strong>Identification & KYC:</strong> Aadhaar numbers (masked in database records, stored with strict role-based access restricted to authorized accounts personnel) and PAN numbers for tax TDS/TCS obligations.</li>
                        <li><strong>Transaction & Weighment Telemetry:</strong> Vehicle registration numbers, gross weighment, tare weighment, moisture readings, dockage/refraction percentages, and payment vouchers.</li>
                        <li><strong>Commercial Buyer Data:</strong> Entity name, GSTIN, PAN, business addresses, designated dispatch points, and authorized signatory contacts.</li>
                    </ul>

                    <h4 class="fw-bold text-dark mt-4 mb-3">3. How Your Information is Used</h4>
                    <p class="text-muted mb-2">We process information exclusively for legitimate agricultural operations, including:</p>
                    <ul class="text-muted mb-3">
                        <li>Issuing computerized weighbridge purchase receipts and sales tax invoices under Section 31 of the CGST Act.</li>
                        <li>Remitting timely payment settlements directly to farmer bank accounts.</li>
                        <li>Communicating daily mandi procurement rates, harvest updates, and loading confirmations via SMS or phone calls.</li>
                        <li>Maintaining mandatory statutory audit trails and books of accounts under the Agricultural Produce Market Committee (APMC) and Income Tax guidelines.</li>
                    </ul>

                    <h4 class="fw-bold text-dark mt-4 mb-3">4. Strict Non-Disclosure & Zero Data Sale</h4>
                    <p class="text-muted leading-relaxed">
                        {{ setting('website.brand_name', 'KisanHub') }} does <strong>never sell, lease, monetize, or disclose</strong> farmer rosters, contact databases, or financial numbers to marketing agencies, data brokers, or third-party lead aggregators. Data is accessed solely by authenticated personnel on a strict need-to-know basis governed by role permissions.
                    </p>

                    <h4 class="fw-bold text-dark mt-4 mb-3">5. Data Security Measures</h4>
                    <p class="text-muted leading-relaxed">
                        All transaction data is stored in secured relational databases with TLS encrypted network transit, hashed credentials, immutable audit logs for transaction reversals, and periodic encrypted offsite database backups.
                    </p>

                    <h4 class="fw-bold text-dark mt-4 mb-3">6. Grievance & Inquiries</h4>
                    <p class="text-muted leading-relaxed">
                        If you have any questions or wish to correct your registered bank or address details, please contact our trading desk at 
                        <a href="mailto:{{ setting('business.email', 'trade@kisanenterprises.com') }}" class="text-decoration-none fw-semibold">
                            {{ setting('business.email', 'trade@kisanenterprises.com') }}
                        </a> 
                        or visit our weighbridge office with your original passbook.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
