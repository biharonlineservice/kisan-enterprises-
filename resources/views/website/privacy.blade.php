@extends('layouts.website')

@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('website.brand_name', 'KisanHub');
@endphp

@section('title', ($isHindi ? 'गोपनीयता नीति — ' : 'Privacy Policy — Farmer & Trade Partner Data Protection | ') . $bizName)
@section('meta_description', 'Privacy policy and data governance practices of ' . $bizName . ' regarding farmer registration, banking details, and commercial trading records.')

@section('content')
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start justify-content-between g-3">
            <div class="col-12 col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(33, 134, 75, 0.10); border: 1px solid rgba(33, 134, 75, 0.22); color: #145a32; font-weight: 600; font-size: 0.82rem;">
                    <i class="bi bi-shield-lock-fill text-success"></i>
                    <span>{{ $isHindi ? 'डेटा सुरक्षा एवं गोपनीयता मानक' : 'Data Protection & Governance' }}</span>
                </div>
                <h1 class="fw-bold text-dark mb-2" style="font-size: 1.9rem; line-height: 1.24; letter-spacing: -0.02em;">
                    {{ $isHindi ? 'गोपनीयता नीति (Privacy Policy)' : 'Privacy Policy & Data Governance' }}
                </h1>
                <p class="text-muted mb-0">
                    {{ $isHindi ? 'किसान पंजीकरण, बैंक खाता सुरक्षा एवं व्यापारिक अभिलेखों की गोपनीयता' : 'How we safeguard farmer KYC, banking credentials, and weighbridge transaction records' }}
                </p>
            </div>
            <div class="col-12 col-lg-4 d-flex justify-content-lg-end align-items-start">
                <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-calendar-check text-success me-1"></i>
                    {{ $isHindi ? 'अंतिम अपडेट: ' : 'Updated: ' }}{{ date('F Y') }}
                </span>
            </div>
        </div>
    </div>
</section>

<div class="container py-4 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    {{-- Key Trust Highlights --}}
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-3">
                                <i class="bi bi-lock-fill text-success fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $isHindi ? 'सुरक्षित बैंक विवरण' : 'Encrypted Banking KYC' }}</div>
                                    <small class="text-muted">{{ $isHindi ? 'केवल RTGS/NEFT फसल भुगतान हेतु उपयोग' : 'Used strictly for RTGS/NEFT crop settlements' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-3">
                                <i class="bi bi-shield-slash-fill text-primary fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $isHindi ? 'शून्य डेटा बिक्री' : 'Zero Third-Party Sale' }}</div>
                                    <small class="text-muted">{{ $isHindi ? 'किसान डेटा किसी बाहरी एजेंसी को नहीं दिया जाता' : 'We never sell or rent farmer phone or bank lists' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-3">
                                <i class="bi bi-file-earmark-check-fill text-warning-emphasis fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $isHindi ? 'वैधानिक मंडी अनुपालन' : 'Statutory Audit Trail' }}</div>
                                    <small class="text-muted">{{ $isHindi ? 'APMC एवं GST नियमों के अनुरूप रिकॉर्ड' : 'Compliant with APMC & GST record standards' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">1</span>
                        <span>Commitment to Data Confidentiality</span>
                    </h4>
                    <p class="text-muted leading-relaxed">
                        At <strong>{{ $bizName }}</strong> (a digital agri-trading platform powered by <strong>{{ setting('business.name', 'Kisan Enterprises') }}</strong>, "we", "our", or "the Platform"), we recognize that the personal and financial information entrusted to us by farmers, agricultural producers, rice millers, and trade partners is sensitive and critical. This policy details how we collect, handle, store, and safeguard your data across our physical weighbridge operations, ERP system, and public web portals.
                    </p>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">2</span>
                        <span>Information We Collect</span>
                    </h4>
                    <p class="text-muted mb-2">In the ordinary course of agricultural trade and statutory compliance, we collect:</p>
                    <ul class="text-muted mb-3 d-grid gap-2">
                        <li><strong>Farmer Enrollment Data:</strong> Full name, father's/husband's name, mobile number, village, post office, tehsil, district, state, and pincode.</li>
                        <li><strong>Financial & Payment Credentials:</strong> Bank account number, IFSC code, and bank branch name—collected exclusively for remitting crop sale proceeds via RTGS/NEFT/IMPS.</li>
                        <li><strong>Identification & KYC:</strong> Aadhaar numbers (masked in database records, stored with strict role-based access restricted to authorized accounts personnel) and PAN numbers for tax TDS/TCS obligations.</li>
                        <li><strong>Transaction & Weighment Telemetry:</strong> Vehicle registration numbers, gross weighment, tare weighment, moisture readings, dockage/refraction percentages, and payment vouchers.</li>
                        <li><strong>Commercial Buyer Data:</strong> Entity name, GSTIN, PAN, business addresses, designated dispatch points, and authorized signatory contacts.</li>
                    </ul>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">3</span>
                        <span>How Your Information is Used</span>
                    </h4>
                    <p class="text-muted mb-2">We process information exclusively for legitimate agricultural operations, including:</p>
                    <ul class="text-muted mb-3 d-grid gap-2">
                        <li>Issuing computerized weighbridge purchase receipts and sales tax invoices under Section 31 of the CGST Act.</li>
                        <li>Remitting timely payment settlements directly to farmer bank accounts.</li>
                        <li>Communicating daily mandi procurement rates, harvest updates, and loading confirmations via SMS or phone calls.</li>
                        <li>Maintaining mandatory statutory audit trails and books of accounts under the Agricultural Produce Market Committee (APMC) and Income Tax guidelines.</li>
                    </ul>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">4</span>
                        <span>Strict Non-Disclosure & Zero Data Sale</span>
                    </h4>
                    <p class="text-muted leading-relaxed">
                        {{ $bizName }} does <strong>never sell, lease, monetize, or disclose</strong> farmer rosters, contact databases, or financial numbers to marketing agencies, data brokers, or third-party lead aggregators. Data is accessed solely by authenticated personnel on a strict need-to-know basis governed by role permissions.
                    </p>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">5</span>
                        <span>Data Security Measures</span>
                    </h4>
                    <p class="text-muted leading-relaxed">
                        All transaction data is stored in secured relational databases with TLS encrypted network transit, hashed credentials, immutable audit logs for transaction reversals, and periodic encrypted offsite database backups.
                    </p>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">6</span>
                        <span>Grievance & Inquiries</span>
                    </h4>
                    <p class="text-muted leading-relaxed mb-0">
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
