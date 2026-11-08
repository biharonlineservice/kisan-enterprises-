@extends('layouts.website')

@section('title', 'Terms of Trade — Agricultural Procurement & Grain Supply')
@section('meta_description', 'Official commercial terms, weighbridge regulations, quality inspection standards, and settlement guidelines of KisanHub.')

@section('content')
<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Terms of Trade</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold text-dark mb-1">Commercial Terms of Trade</h1>
        <p class="text-muted mb-0">Applicable to all Farmer Procurement, Grain Storage, and Wholesale Dispatches</p>
    </div>
</div>

<div class="container py-2 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h4 class="fw-bold text-dark mb-3">1. Scope of Operations</h4>
                    <p class="text-muted leading-relaxed">
                        These Terms of Trade govern all transactions conducted through the <strong>{{ setting('website.brand_name', 'KisanHub') }}</strong> platform and physical trading operations of <strong>{{ setting('business.name', 'Kisan Enterprises') }}</strong> ("the Enterprise"), including direct crop procurement from agricultural producers ("Farmers") and bulk commodity sales to rice mills, flour mills, food processors, and wholesale merchants ("Buyers").
                    </p>

                    <h4 class="fw-bold text-dark mt-4 mb-3">2. Procurement & Weighbridge Protocol</h4>
                    <ul class="text-muted mb-3">
                        <li><strong>Computerized Weighment:</strong> All tractor-trolleys, pickup trucks, and commercial lorries must undergo digital gross weighment upon entry and tare weighment upon unloading at the designated weighbridge. Both readings are printed immediately on the official Purchase Receipt slip.</li>
                        <li><strong>Tare Deductions:</strong> Bag tare deductions are applied uniformly: standard gunny bags at 1.0 kg/bag; HDPE/plastic bags at 0.15 kg/bag unless otherwise agreed upon.</li>
                        <li><strong>Quality & Moisture Adjustments:</strong> Representative crop samples are drawn for moisture analysis. If moisture content exceeds baseline trade standards (e.g., standard 14% for paddy/wheat), standard moisture cut formulas are applied in accordance with prevailing APMC mandi conventions.</li>
                        <li><strong>Farmer Receipt & Transparency:</strong> Every farmer receives an authorized computer-generated Purchase Voucher detailing gross weight, tare weight, net weight, rate per quintal, gross value, deductions, and net payable balance.</li>
                    </ul>

                    <h4 class="fw-bold text-dark mt-4 mb-3">3. Payment Settlement for Farmers</h4>
                    <ul class="text-muted mb-3">
                        <li>Payment shall be remitted to the farmer via electronic bank transfer (RTGS / NEFT / IMPS), Account Payee Cheque, or direct cash voucher within the mutually agreed timeline, subject to statutory limits under the Income Tax Act.</li>
                        <li>Farmers are responsible for ensuring accuracy of their bank account details provided during registration.</li>
                    </ul>

                    <h4 class="fw-bold text-dark mt-4 mb-3">4. Sales & Wholesale Dispatch Terms</h4>
                    <ul class="text-muted mb-3">
                        <li><strong>Sales Orders & Invoicing:</strong> No goods are released from storage without an approved Sales Order and an official Sales Tax Invoice conforming to GST rules.</li>
                        <li><strong>Delivery Terms:</strong> Unless explicitly agreed otherwise as "Delivered / FOR Destination", all sales quotations are "Ex-Godown / Ex-Weighbridge". Freight, in-transit insurance, and unloading charges at the buyer's destination mill are to the buyer's account.</li>
                        <li><strong>Inspection at Loading:</strong> The buyer or their authorized representative has the right and responsibility to inspect grain quality, count bags, and verify weighbridge records prior to truck departure. Once the vehicle departs the Enterprise premises, complaints regarding shortage or exterior damage cannot be entertained.</li>
                        <li><strong>Credit Period & Interest:</strong> Approved wholesale credit limits and payment windows (e.g., 7, 15, or 30 days) must be strictly honored. Overdue invoices beyond the agreed credit period may attract commercial interest at 18% per annum until full realization.</li>
                    </ul>

                    <h4 class="fw-bold text-dark mt-4 mb-3">5. Force Majeure</h4>
                    <p class="text-muted leading-relaxed">
                        Neither party shall be held liable for failure or delay in fulfilling procurement or delivery obligations if such delay results from acts of God, extreme unseasonal weather (hailstorms, floods), transport strikes, road blockades, government export/movement restrictions, or pandemic lockdown mandates.
                    </p>

                    <h4 class="fw-bold text-dark mt-4 mb-3">6. Dispute Resolution & Jurisdiction</h4>
                    <p class="text-muted leading-relaxed">
                        Any unresolved trade dispute arising under or in connection with transactions with the Enterprise shall be subject to the exclusive jurisdiction of the competent courts in 
                        <strong>{{ setting('business.district', 'Chandauli') }} / {{ setting('business.state', 'Uttar Pradesh') }}</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
