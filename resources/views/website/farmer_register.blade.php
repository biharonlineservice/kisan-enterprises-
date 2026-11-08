@extends('layouts.website')

@section('title', 'Farmer Registration — Direct Crop Procurement')
@section('meta_description', 'Register online with KisanHub for transparent electronic weighment, fair market pricing, and direct bank settlement for your harvest.')

@section('content')
<div class="bg-white border-bottom py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 small">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Farmer Registration</li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold text-dark mb-1">Farmer Online Registration</h1>
                <p class="text-muted mb-0">
                    Enroll once with KisanHub to receive a permanent Farmer ID, priority weighbridge unloading, and direct bank account settlement.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-success-subtle text-success px-3 py-2 fs-6 border border-success-subtle">
                    <i class="bi bi-shield-check me-1"></i> Zero Registration Fee
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container py-2 pb-5">
    <div class="row g-4">
        {{-- Registration Form --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-success"></i>
                        Farmer Enrollment Form
                    </h5>
                    <small class="text-muted">Please fill in accurate details matching your Aadhaar and bank passbook.</small>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the errors below:</h6>
                            <ul class="mb-0 small ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('website.farmer.register.store') }}" method="POST">
                        @csrf

                        {{-- Section 1: Personal Details --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <span class="badge bg-primary me-2">1</span> Personal Details
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Farmer Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" placeholder="As per Aadhaar Card" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Father's / Husband's Name</label>
                                    <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror" 
                                           value="{{ old('father_name') }}" placeholder="Father / Husband Name">
                                    @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Mobile Number (WhatsApp/SMS) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">+91</span>
                                        <input type="text" name="mobile" inputmode="numeric" class="form-control @error('mobile') is-invalid @enderror" 
                                               value="{{ old('mobile') }}" placeholder="10-digit mobile" maxlength="10" required>
                                    </div>
                                    @error('mobile') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alternate Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">+91</span>
                                        <input type="text" name="alt_mobile" inputmode="numeric" class="form-control @error('alt_mobile') is-invalid @enderror" 
                                               value="{{ old('alt_mobile') }}" placeholder="Optional second number" maxlength="10">
                                    </div>
                                    @error('alt_mobile') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Address & Location --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <span class="badge bg-primary me-2">2</span> Village & Tehsil Location
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Village / Gram <span class="text-danger">*</span></label>
                                    <input type="text" name="village" class="form-control @error('village') is-invalid @enderror" 
                                           value="{{ old('village') }}" placeholder="Village Name" required>
                                    @error('village') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Post Office</label>
                                    <input type="text" name="post_office" class="form-control @error('post_office') is-invalid @enderror" 
                                           value="{{ old('post_office') }}" placeholder="Post Office">
                                    @error('post_office') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">Tehsil / Block <span class="text-danger">*</span></label>
                                    <input type="text" name="tehsil" class="form-control @error('tehsil') is-invalid @enderror" 
                                           value="{{ old('tehsil') }}" placeholder="Tehsil Name" required>
                                    @error('tehsil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">District <span class="text-danger">*</span></label>
                                    <input type="text" name="district" class="form-control @error('district') is-invalid @enderror" 
                                           value="{{ old('district', 'Chandauli') }}" required>
                                    @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label required">State <span class="text-danger">*</span></label>
                                    <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" 
                                           value="{{ old('state', 'Uttar Pradesh') }}" required>
                                    @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PIN Code</label>
                                    <input type="text" name="pincode" inputmode="numeric" class="form-control @error('pincode') is-invalid @enderror" 
                                           value="{{ old('pincode') }}" placeholder="6-digit PIN" maxlength="6">
                                    @error('pincode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Crops & Acreage --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <span class="badge bg-primary me-2">3</span> Agricultural Produce Profile
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Major Crops Cultivated</label>
                                    <input type="text" name="crops_grown" class="form-control @error('crops_grown') is-invalid @enderror" 
                                           value="{{ old('crops_grown') }}" placeholder="e.g. Paddy (1509/1121), Wheat, Mustard">
                                    <small class="text-muted">Mention the crops or varieties you plan to bring for procurement.</small>
                                    @error('crops_grown') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Estimated Land Acreage</label>
                                    <div class="input-group">
                                        <input type="number" step="0.5" min="0" inputmode="decimal" name="estimated_acreage" 
                                               class="form-control @error('estimated_acreage') is-invalid @enderror" 
                                               value="{{ old('estimated_acreage') }}" placeholder="e.g. 5.0">
                                        <span class="input-group-text bg-light">Acres</span>
                                    </div>
                                    @error('estimated_acreage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Bank Details & KYC --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <span class="badge bg-primary me-2">4</span> Direct Bank Settlement Details (Optional but Recommended)
                            </h6>
                            <div class="alert alert-light border small text-muted mb-3">
                                <i class="bi bi-shield-lock-fill text-success me-1"></i>
                                Bank account details are encrypted and securely used solely for direct RTGS/NEFT payment transfers when you sell crops.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Aadhaar Card Number (12 Digits)</label>
                                    <input type="password" name="aadhaar_number" inputmode="numeric" class="form-control @error('aadhaar_number') is-invalid @enderror" 
                                           value="{{ old('aadhaar_number') }}" placeholder="12-digit Aadhaar number" maxlength="12">
                                    <small class="text-muted">Masked for privacy. Used for KYC verification at procurement center.</small>
                                    @error('aadhaar_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                                           value="{{ old('bank_name') }}" placeholder="e.g. State Bank of India, Baroda UP Bank">
                                    @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Account Number</label>
                                    <input type="text" name="bank_account_number" inputmode="numeric" class="form-control @error('bank_account_number') is-invalid @enderror" 
                                           value="{{ old('bank_account_number') }}" placeholder="Account Number">
                                    @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank IFSC Code</label>
                                    <input type="text" name="bank_ifsc" class="form-control text-uppercase @error('bank_ifsc') is-invalid @enderror" 
                                           value="{{ old('bank_ifsc') }}" placeholder="11-character IFSC (e.g. SBIN0001234)" maxlength="11">
                                    @error('bank_ifsc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Declaration --}}
                        <div class="form-check mb-4 bg-light p-3 rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="termsCheck" required>
                            <label class="form-check-label small text-muted" for="termsCheck">
                                I confirm that the details provided are correct and belong to me. I agree to KisanHub's weighment standards and procurement procedures.
                            </label>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                            <a href="{{ route('website.procurement') }}" class="btn btn-outline-secondary order-2 order-sm-1">
                                &larr; Back to Procurement Info
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold order-1 order-sm-2">
                                <i class="bi bi-check2-circle me-1"></i> Submit Farmer Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar Information --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-gift me-1"></i> Why Register with Us?
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex gap-3 mb-3">
                            <div class="text-success fs-4 lh-1"><i class="bi bi-speedometer2"></i></div>
                            <div>
                                <strong class="d-block text-dark">Fast Unloading</strong>
                                <small class="text-muted">Registered farmers bypass entry queue paperwork with their pre-issued Farmer ID card.</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3 mb-3">
                            <div class="text-success fs-4 lh-1"><i class="bi bi-receipt"></i></div>
                            <div>
                                <strong class="d-block text-dark">Computerized Weighment</strong>
                                <small class="text-muted">Gross and tare weighment slip issued instantly with gross weight, moisture deduction, and net payable amount.</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3 mb-3">
                            <div class="text-success fs-4 lh-1"><i class="bi bi-bank"></i></div>
                            <div>
                                <strong class="d-block text-dark">Direct Account Settlement</strong>
                                <small class="text-muted">Direct transfer to your bank account via RTGS/NEFT or instant settlement voucher as per your preference.</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <div class="text-success fs-4 lh-1"><i class="bi bi-bell"></i></div>
                            <div>
                                <strong class="d-block text-dark">Mandi Rate Updates</strong>
                                <small class="text-muted">Receive daily mandi procurement opening rates directly on your registered mobile number.</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 text-center">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-headset fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Need Help Registering?</h6>
                    <p class="small text-muted mb-3">
                        Visit our procurement hub weighbridge office in person or call our trading desk. Our staff will register your profile on the spot.
                    </p>
                    @if(setting('business.phone'))
                        <a href="tel:{{ setting('business.phone') }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-telephone-fill me-1"></i> {{ setting('business.phone') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
