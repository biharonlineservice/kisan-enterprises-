@extends('layouts.app')

@section('title', $customer->exists ? 'Edit Customer: ' . $customer->name : 'Add Customer / Mill')

@section('content')
<div class="page-toolbar">
    <div>
        <h2 class="h5 fw-bold mb-1">{{ $customer->exists ? 'Edit Customer Details' : 'Register Customer / Rice Mill' }}</h2>
        <p class="subtitle">Maintain buyers, rice mills, wholesalers, tax identifiers, and credit terms.</p>
    </div>
    <a href="{{ route('customers.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left"></i> Back to Customers
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header bg-white">
                <span class="fw-bold">{{ $customer->exists ? 'Customer ID: ' . $customer->customer_id : 'New Customer Account' }}</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" novalidate>
                    @csrf
                    @if($customer->exists)
                        @method('PUT')
                    @endif
                    <x-form-token />

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-building me-1"></i> Business Profile</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Business / Customer Name <span class="req">*</span></label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $customer->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="e.g. Mahavir Rice Mill Ltd." 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="customer_type" class="form-label">Business Category <span class="req">*</span></label>
                            <select id="customer_type" name="customer_type" class="form-select @error('customer_type') is-invalid @enderror" required>
                                @foreach($types as $typeKey => $typeName)
                                    <option value="{{ $typeKey }}" {{ old('customer_type', $customer->customer_type) === $typeKey ? 'selected' : '' }}>
                                        {{ $typeName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" 
                                   id="contact_person" 
                                   name="contact_person" 
                                   value="{{ old('contact_person', $customer->contact_person) }}" 
                                   class="form-control" 
                                   placeholder="e.g. Rajesh Agarwal">
                        </div>

                        <div class="col-md-4">
                            <label for="mobile" class="form-label">Primary Mobile</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="text" 
                                       id="mobile" 
                                       name="mobile" 
                                       value="{{ old('mobile', $customer->mobile) }}" 
                                       class="form-control @error('mobile') is-invalid @enderror" 
                                       placeholder="10-digit mobile" 
                                       maxlength="10">
                            </div>
                            @error('mobile')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="alt_phone" class="form-label">Alternate / Office Phone</label>
                            <input type="text" 
                                   id="alt_phone" 
                                   name="alt_phone" 
                                   value="{{ old('alt_phone', $customer->alt_phone) }}" 
                                   class="form-control" 
                                   placeholder="e.g. 0612-2500123">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $customer->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="orders@ricemill.com">
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="city" class="form-label">City / Town</label>
                            <input type="text" 
                                   id="city" 
                                   name="city" 
                                   value="{{ old('city', $customer->city) }}" 
                                   class="form-control" 
                                   placeholder="e.g. Patna, Sasaram">
                        </div>

                        <div class="col-md-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" 
                                   id="state" 
                                   name="state" 
                                   value="{{ old('state', $customer->state) }}" 
                                   class="form-control" 
                                   placeholder="e.g. Bihar">
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Premises / Mill Street Address</label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address', $customer->address) }}" 
                                   class="form-control" 
                                   placeholder="Industrial Area, Mandi road, pin code">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-check me-1"></i> Tax & Credit Terms</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="gstin" class="form-label">GSTIN (Optional)</label>
                            <input type="text" 
                                   id="gstin" 
                                   name="gstin" 
                                   value="{{ old('gstin', $customer->gstin) }}" 
                                   class="form-control text-uppercase font-monospace @error('gstin') is-invalid @enderror" 
                                   placeholder="e.g. 10IYLPK1340A1Z3" 
                                   autocapitalize="characters"
                                   spellcheck="false"
                                   oninput="this.value = this.value.toUpperCase().replace(/\s+/g, ''); if (this.value.length === 15) { var panEl = document.getElementById('pan'); if (panEl && !panEl.value) panEl.value = this.value.substring(2, 12); }"
                                   maxlength="15">
                            @error('gstin')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave blank if the buyer is unregistered.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="pan" class="form-label">PAN Number</label>
                            <input type="text" 
                                   id="pan" 
                                   name="pan" 
                                   value="{{ old('pan', $customer->pan) }}" 
                                   class="form-control text-uppercase font-monospace @error('pan') is-invalid @enderror" 
                                   placeholder="e.g. IYLPK1340A" 
                                   autocapitalize="characters"
                                   spellcheck="false"
                                   oninput="this.value = this.value.toUpperCase().replace(/\s+/g, '')"
                                   maxlength="10">
                            @error('pan')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="credit_limit" class="form-label">Authorized Credit Limit (₹)</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   id="credit_limit" 
                                   name="credit_limit" 
                                   value="{{ old('credit_limit', $customer->credit_limit ?? 0.00) }}" 
                                   class="form-control @error('credit_limit') is-invalid @enderror">
                            @error('credit_limit')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">0.00 for strictly cash-and-carry buyers.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="payment_terms_days" class="form-label">Payment Terms (Days) <span class="req">*</span></label>
                            <input type="number" 
                                   min="0" 
                                   max="365" 
                                   id="payment_terms_days" 
                                   name="payment_terms_days" 
                                   value="{{ old('payment_terms_days', $customer->payment_terms_days ?? 15) }}" 
                                   class="form-control @error('payment_terms_days') is-invalid @enderror" 
                                   required>
                            @error('payment_terms_days')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="opening_balance" class="form-label">Opening Balance (₹)</label>
                            <input type="number" 
                                   step="0.01" 
                                   id="opening_balance" 
                                   name="opening_balance" 
                                   value="{{ old('opening_balance', $customer->opening_balance ?? 0.00) }}" 
                                   class="form-control @error('opening_balance') is-invalid @enderror">
                            @error('opening_balance')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Positive (+) if customer owes money; negative (-) if advance deposit held.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Internal Business Notes</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="2" 
                                  class="form-control" 
                                  placeholder="Milling capacity, preferred grain quality, bank details...">{{ old('notes', $customer->notes) }}</textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="is_active">Customer account is active for sales orders</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2"></i> {{ $customer->exists ? 'Save Changes' : 'Create Customer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
