@extends('layouts.website')

@php
    $isHindi = app()->getLocale() === 'hi';
    $bizName = setting('website.brand_name', 'KisanHub');
@endphp

@section('title', ($isHindi ? 'व्यापार की शर्तें — ' : 'Terms of Trade — Agricultural Procurement & Grain Supply | ') . $bizName)
@section('meta_description', $isHindi
    ? $bizName . ' के आधिकारिक व्यापारिक नियम, इलेक्ट्रॉनिक धर्मकांटा तौल मानक, नमी एवं गुणवत्ता जांच और किसान भुगतान की शर्तें।'
    : 'Official commercial terms, weighbridge regulations, quality inspection standards, and settlement guidelines of ' . $bizName . '.')

@section('content')
<section class="hero-section">
    <div class="container">
        <div class="row align-items-start justify-content-between g-3">
            <div class="col-12 col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(33, 134, 75, 0.10); border: 1px solid rgba(33, 134, 75, 0.22); color: #145a32; font-weight: 600; font-size: 0.82rem;">
                    <i class="bi bi-file-earmark-ruled-fill text-success"></i>
                    <span>{{ $isHindi ? 'मंडी खरीद एवं थोक आपूर्ति नियम' : 'Standard Mandi & B2B Trade Governance' }}</span>
                </div>
                <h1 class="fw-bold text-dark mb-2" style="font-size: 1.9rem; line-height: 1.24; letter-spacing: -0.02em;">
                    {{ $isHindi ? 'व्यापार की शर्तें (Terms of Trade)' : 'Commercial Terms of Trade' }}
                </h1>
                <p class="text-muted mb-0">
                    {{ $isHindi ? 'किसान फसल खरीद, धर्मकांटा तौल, गोदाम भंडारण एवं थोक मिल आपूर्ति हेतु आधिकारिक दिशा-निर्देश' : 'Applicable to all Farmer Procurement, Digital Weighment, Grain Storage, and Wholesale Dispatches' }}
                </p>
            </div>
            <div class="col-12 col-lg-4 d-flex justify-content-lg-end align-items-start">
                <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 fw-semibold">
                    <i class="bi bi-patch-check-fill text-success me-1"></i>
                    {{ $isHindi ? 'प्रमाणित धर्मकांटा मानक' : 'APMC & GST Compliant' }}
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
                    {{-- Quick Pillar Highlights --}}
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-3">
                                <i class="bi bi-speedometer2 text-success fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $isHindi ? 'डिजिटल धर्मकांटा तौल' : 'Digital Gross & Tare' }}</div>
                                    <small class="text-muted">{{ $isHindi ? 'भरी व खाली गाड़ी की कम्प्यूटराइज्ड पर्ची' : 'Printed computerized weighment receipt' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-3">
                                <i class="bi bi-droplet-half text-primary fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $isHindi ? 'पारदर्शी नमी जांच' : 'Standard Moisture Norms' }}</div>
                                    <small class="text-muted">{{ $isHindi ? 'किसान की उपस्थिति में डिजिटल मीटर जांच' : 'Digital meter testing in farmer presence' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-3">
                                <i class="bi bi-truck text-warning-emphasis fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $isHindi ? 'एक्स-गोदाम थोक डिस्पैच' : 'Ex-Godown GST Dispatch' }}</div>
                                    <small class="text-muted">{{ $isHindi ? 'लोडिंग पूर्व गुणवत्ता व वजन सत्यापन' : 'Pre-dispatch inspection & E-Way bill' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">1</span>
                        <span>Scope of Operations</span>
                    </h4>
                    <p class="text-muted leading-relaxed">
                        These Terms of Trade govern all transactions conducted through the <strong>{{ $bizName }}</strong> platform and physical trading operations of <strong>{{ setting('business.name', 'Kisan Enterprises') }}</strong> ("the Enterprise"), including direct crop procurement from agricultural producers ("Farmers") and bulk commodity sales to rice mills, flour mills, food processors, and wholesale merchants ("Buyers").
                    </p>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">2</span>
                        <span>Procurement & Weighbridge Protocol</span>
                    </h4>
                    <ul class="text-muted mb-3 d-grid gap-2">
                        <li><strong>Computerized Weighment:</strong> All tractor-trolleys, pickup trucks, and commercial lorries must undergo digital gross weighment upon entry and tare weighment upon unloading at the designated weighbridge. Both readings are printed immediately on the official Purchase Receipt slip.</li>
                        <li><strong>Tare Deductions:</strong> Bag tare deductions are applied uniformly: standard gunny bags at 1.0 kg/bag; HDPE/plastic bags at 0.15 kg/bag unless otherwise agreed upon.</li>
                        <li><strong>Quality & Moisture Adjustments:</strong> Representative crop samples are drawn for moisture analysis. If moisture content exceeds baseline trade standards (e.g., standard 14% for paddy/wheat), standard moisture cut formulas are applied in accordance with prevailing APMC mandi conventions.</li>
                        <li><strong>Farmer Receipt & Transparency:</strong> Every farmer receives an authorized computer-generated Purchase Voucher detailing gross weight, tare weight, net weight, rate per quintal, gross value, deductions, and net payable balance.</li>
                    </ul>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">3</span>
                        <span>Payment Settlement for Farmers</span>
                    </h4>
                    <ul class="text-muted mb-3 d-grid gap-2">
                        <li>Payment shall be remitted to the farmer via electronic bank transfer (RTGS / NEFT / IMPS), Account Payee Cheque, or direct cash voucher within the mutually agreed timeline, subject to statutory limits under the Income Tax Act.</li>
                        <li>Farmers are responsible for ensuring accuracy of their bank account details provided during registration.</li>
                    </ul>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">4</span>
                        <span>Sales & Wholesale Dispatch Terms</span>
                    </h4>
                    <ul class="text-muted mb-3 d-grid gap-2">
                        <li><strong>Sales Orders & Invoicing:</strong> No goods are released from storage without an approved Sales Order and an official Sales Tax Invoice conforming to GST rules.</li>
                        <li><strong>Delivery Terms:</strong> Unless explicitly agreed otherwise as "Delivered / FOR Destination", all sales quotations are "Ex-Godown / Ex-Weighbridge". Freight, in-transit insurance, and unloading charges at the buyer's destination mill are to the buyer's account.</li>
                        <li><strong>Inspection at Loading:</strong> The buyer or their authorized representative has the right and responsibility to inspect grain quality, count bags, and verify weighbridge records prior to truck departure. Once the vehicle departs the Enterprise premises, complaints regarding shortage or exterior damage cannot be entertained.</li>
                        <li><strong>Credit Period & Interest:</strong> Approved wholesale credit limits and payment windows (e.g., 7, 15, or 30 days) must be strictly honored. Overdue invoices beyond the agreed credit period may attract commercial interest at 18% per annum until full realization.</li>
                    </ul>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">5</span>
                        <span>Force Majeure</span>
                    </h4>
                    <p class="text-muted leading-relaxed">
                        Neither party shall be held liable for failure or delay in fulfilling procurement or delivery obligations if such delay results from acts of God, extreme unseasonal weather (hailstorms, floods), transport strikes, road blockades, government export/movement restrictions, or pandemic lockdown mandates.
                    </p>

                    <h4 class="h5 fw-bold text-dark mt-4 mb-3 d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success-subtle text-success">6</span>
                        <span>Dispute Resolution & Jurisdiction</span>
                    </h4>
                    <p class="text-muted leading-relaxed mb-0">
                        Any unresolved trade dispute arising under or in connection with transactions with the Enterprise shall be subject to the exclusive jurisdiction of the competent courts in
                        <strong>{{ setting('business.district', 'Chandauli') }} / {{ setting('business.state', 'Uttar Pradesh') }}</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
