@extends('layouts.app')

@section('title', $farmer->exists ? __('procurement.farmers.edit') . ': ' . $farmer->name : __('procurement.farmers.add_new'))

@section('content')
<div class="erp-hero-banner">
    <div class="position-relative z-1 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip">
                    <i class="bi bi-person-plus-fill text-warning"></i>
                    {{ $farmer->exists ? $farmer->farmer_id : 'Auto-ID (FRM...)' }}
                </span>
            </div>
            <h2 class="h4 fw-bold mb-1 text-white">
                {{ $farmer->exists ? __('procurement.farmers.edit') : __('procurement.farmers.add_new') }}
            </h2>
            <p class="mb-0 small" style="color: rgba(255, 255, 255, .82);">
                Maintain accurate farmer personal, village, bank settlement, and delayed payment bonus preferences.
            </p>
        </div>
        <div>
            <a href="{{ $farmer->exists ? route('farmers.show', $farmer) : route('farmers.index') }}" class="erp-hero-btn">
                <i class="bi bi-arrow-left"></i>
                <span>{{ __('procurement.farmers.all_farmers_back') }}</span>
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-10">
        <form method="POST" action="{{ $farmer->exists ? route('farmers.update', $farmer) : route('farmers.store') }}" novalidate>
            @csrf
            @if($farmer->exists)
                @method('PUT')
            @endif
            <x-form-token />

            {{-- Section 1: Personal & Ledger Info --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold d-flex align-items-center gap-2">
                        <span class="kpi-badge-icon" style="width: 30px; height: 30px; font-size: .95rem; background: #E8F3EC; color: #21864B; border-radius: 8px; display: inline-grid; place-items: center;">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <span>{{ __('procurement.farmers.details') }}</span>
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">{{ __('procurement.farmers.name') }} <span class="req">*</span></label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $farmer->name) }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Ramashankar Singh"
                                   required
                                   autofocus>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="mobile" class="form-label">{{ __('procurement.farmers.mobile') }}</label>
                            <div class="input-group">
                                <span class="input-group-text fw-semibold">+91</span>
                                <input type="tel"
                                       inputmode="numeric"
                                       id="mobile"
                                       name="mobile"
                                       value="{{ old('mobile', $farmer->mobile) }}"
                                       class="form-control @error('mobile') is-invalid @enderror"
                                       placeholder="10-digit mobile number"
                                       maxlength="10">
                            </div>
                            @error('mobile')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="registration_date" class="form-label">Registration Date</label>
                            <input type="date"
                                   id="registration_date"
                                   name="registration_date"
                                   value="{{ old('registration_date', $farmer->registration_date ? $farmer->registration_date->format('Y-m-d') : now()->toDateString()) }}"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="opening_balance" class="form-label">Opening Balance (₹)</label>
                            <input type="number"
                                   step="0.01"
                                   inputmode="decimal"
                                   id="opening_balance"
                                   name="opening_balance"
                                   value="{{ old('opening_balance', $farmer->opening_balance ?? 0.00) }}"
                                   class="form-control @error('opening_balance') is-invalid @enderror">
                            @error('opening_balance')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Positive (+) if mandi owes farmer; negative (-) if advance given.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Village & Agricultural Location --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <span class="fw-bold d-flex align-items-center gap-2">
                        <span class="kpi-badge-icon" style="width: 30px; height: 30px; font-size: .95rem; background: #FEF6E4; color: #B45309; border-radius: 8px; display: inline-grid; place-items: center;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <span>{{ __('procurement.farmers.land_info') }}</span>
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="village" class="form-label">{{ __('procurement.farmers.village') }}</label>
                            <input type="text"
                                   id="village"
                                   name="village"
                                   value="{{ old('village', $farmer->village) }}"
                                   class="form-control"
                                   placeholder="e.g. Rampur">
                        </div>

                        <div class="col-md-4">
                            <label for="panchayat" class="form-label">{{ __('procurement.farmers.panchayat') }}</label>
                            <input type="text"
                                   id="panchayat"
                                   name="panchayat"
                                   value="{{ old('panchayat', $farmer->panchayat) }}"
                                   class="form-control"
                                   placeholder="e.g. Rampur Paschim">
                        </div>

                        <div class="col-md-4">
                            <label for="block" class="form-label">{{ __('procurement.farmers.block_tehsil') }}</label>
                            <input type="text"
                                   id="block"
                                   name="block"
                                   value="{{ old('block', $farmer->block) }}"
                                   class="form-control"
                                   placeholder="e.g. Bikramganj">
                        </div>

                        <div class="col-md-6">
                            <label for="district" class="form-label">{{ __('procurement.farmers.district') }}</label>
                            <input type="text"
                                   id="district"
                                   name="district"
                                   value="{{ old('district', $farmer->district) }}"
                                   class="form-control"
                                   placeholder="e.g. Rohtas">
                        </div>

                        <div class="col-md-6">
                            <label for="state" class="form-label">{{ __('procurement.farmers.state') }}</label>
                            <input type="text"
                                   id="state"
                                   name="state"
                                   value="{{ old('state', $farmer->state) }}"
                                   class="form-control"
                                   placeholder="e.g. Bihar">
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">{{ __('procurement.farmers.full_address') }}</label>
                            <input type="text"
                                   id="address"
                                   name="address"
                                   value="{{ old('address', $farmer->address) }}"
                                   class="form-control"
                                   placeholder="House / Plot / Landmark">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Bank & Settlement KYC (Restricted) --}}
            @can('farmers.bank')
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <span class="fw-bold d-flex align-items-center gap-2">
                            <span class="kpi-badge-icon" style="width: 30px; height: 30px; font-size: .95rem; background: #E0F2FE; color: #0284C7; border-radius: 8px; display: inline-grid; place-items: center;">
                                <i class="bi bi-bank2"></i>
                            </span>
                            <span>{{ __('procurement.farmers.bank_info') }}</span>
                            <span class="badge bg-light text-muted border small">KYC Protected</span>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">{{ __('procurement.farmers.bank_name') }}</label>
                                <input type="text"
                                       id="bank_name"
                                       name="bank_name"
                                       value="{{ old('bank_name', $farmer->bank_name) }}"
                                       class="form-control"
                                       placeholder="e.g. State Bank of India">
                            </div>

                            <div class="col-md-6">
                                <label for="bank_branch" class="form-label">Branch Name</label>
                                <input type="text"
                                       id="bank_branch"
                                       name="bank_branch"
                                       value="{{ old('bank_branch', $farmer->bank_branch) }}"
                                       class="form-control"
                                       placeholder="e.g. Main Branch">
                            </div>

                            <div class="col-md-6">
                                <label for="bank_account_number" class="form-label">{{ __('procurement.farmers.bank_account') }}</label>
                                <input type="text"
                                       inputmode="numeric"
                                       id="bank_account_number"
                                       name="bank_account_number"
                                       value="{{ old('bank_account_number', $farmer->bank_account_number) }}"
                                       class="form-control font-monospace"
                                       placeholder="Enter bank account number">
                            </div>

                            <div class="col-md-6">
                                <label for="bank_ifsc" class="form-label">{{ __('procurement.farmers.bank_ifsc') }}</label>
                                <input type="text"
                                       id="bank_ifsc"
                                       name="bank_ifsc"
                                       value="{{ old('bank_ifsc', $farmer->bank_ifsc) }}"
                                       class="form-control text-uppercase font-monospace @error('bank_ifsc') is-invalid @enderror"
                                       placeholder="e.g. SBIN0001234"
                                       maxlength="11">
                                @error('bank_ifsc')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="id_type" class="form-label">Government ID Type</label>
                                <select id="id_type" name="id_type" class="form-select">
                                    <option value="">None / Not Provided</option>
                                    <option value="Aadhaar" {{ old('id_type', $farmer->id_type) === 'Aadhaar' ? 'selected' : '' }}>Aadhaar Card</option>
                                    <option value="Voter ID" {{ old('id_type', $farmer->id_type) === 'Voter ID' ? 'selected' : '' }}>Voter ID Card</option>
                                    <option value="KCC" {{ old('id_type', $farmer->id_type) === 'KCC' ? 'selected' : '' }}>Kisan Credit Card (KCC)</option>
                                    <option value="PAN" {{ old('id_type', $farmer->id_type) === 'PAN' ? 'selected' : '' }}>PAN Card</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="id_number" class="form-label">ID Number</label>
                                <input type="text"
                                       id="id_number"
                                       name="id_number"
                                       value="{{ old('id_number', $farmer->id_number) }}"
                                       class="form-control"
                                       placeholder="Identification number">
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- Section 4: Admin-Only Farmer Delayed Payment Bonus --}}
            @if(auth()->user()?->isAdmin())
                <input type="hidden" name="farmer_bonus_section_present" value="1">
                <div class="card shadow-sm border-success border-opacity-50 mb-4">
                    <div class="card-header bg-success-subtle bg-opacity-25 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="fw-bold text-success mb-0">
                                <i class="bi bi-gift-fill me-1"></i> Farmer Delayed Payment Bonus
                                <span class="badge bg-white text-success border border-success-subtle ms-1 small">Admin Only</span>
                            </h6>
                            <small class="text-muted">Configure farmer-specific bonus rate & calculation mode when payment is collected after purchase date.</small>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="delayed_bonus_enabled"
                                   name="delayed_bonus_enabled"
                                   value="1"
                                   {{ old('delayed_bonus_enabled', $farmer->delayed_bonus_enabled ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="delayed_bonus_enabled">Enabled</label>
                        </div>
                    </div>
                    <div class="card-body p-4" id="farmer_bonus_fields">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="delayed_bonus_rate" class="form-label">Farmer Bonus Rate (%)</label>
                                <div class="input-group">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           inputmode="decimal"
                                           id="delayed_bonus_rate"
                                           name="delayed_bonus_rate"
                                           value="{{ old('delayed_bonus_rate', $farmer->delayed_bonus_rate) }}"
                                           placeholder="Default ({{ setting('procurement.delayed_bonus_rate', '1.00') }}%)"
                                           class="form-control @error('delayed_bonus_rate') is-invalid @enderror">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('delayed_bonus_rate')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave blank to use global default ({{ setting('procurement.delayed_bonus_rate', '1.00') }}%).</div>
                            </div>

                            <div class="col-md-6">
                                <label for="delayed_bonus_mode" class="form-label">Calculation Mode</label>
                                @php $selectedMode = old('delayed_bonus_mode', $farmer->delayed_bonus_mode); @endphp
                                <select id="delayed_bonus_mode" name="delayed_bonus_mode" class="form-select @error('delayed_bonus_mode') is-invalid @enderror">
                                    <option value="" {{ empty($selectedMode) ? 'selected' : '' }}>
                                        Global Default ({{ ucfirst(setting('procurement.delayed_bonus_mode', 'monthly')) }})
                                    </option>
                                    <option value="monthly" {{ $selectedMode === 'monthly' ? 'selected' : '' }}>Monthly Pro-rata (% per 30 days)</option>
                                    <option value="flat" {{ $selectedMode === 'flat' ? 'selected' : '' }}>Flat Percentage (%)</option>
                                    <option value="custom" {{ $selectedMode === 'custom' ? 'selected' : '' }}>Custom Manual Amount (₹)</option>
                                </select>
                                @error('delayed_bonus_mode')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Section 5: Notes & Active Status --}}
            <div class="card shadow-sm border-0 mb-5 mb-md-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('procurement.farmers.notes_label') }}</label>
                        <textarea id="notes"
                                  name="notes"
                                  rows="2"
                                  class="form-control"
                                  placeholder="Land acreage, preferred crops, payment instructions...">{{ old('notes', $farmer->notes) }}</textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox"
                               class="form-check-input"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $farmer->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="is_active">Farmer is active for procurement</label>
                    </div>

                    <div class="d-none d-md-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('farmers.index') }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2"></i> {{ $farmer->exists ? __('app.save') : __('procurement.farmers.add_new') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sticky Mobile Save Actions --}}
            <div class="mobile-sticky-actions">
                <a href="{{ route('farmers.index') }}" class="btn btn-light border">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2"></i> {{ __('app.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()?->isAdmin())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('delayed_bonus_enabled');
    const fields = document.getElementById('farmer_bonus_fields');
    if (toggle && fields) {
        function syncBonusFields() {
            fields.classList.toggle('opacity-50', !toggle.checked);
        }
        toggle.addEventListener('change', syncBonusFields);
        syncBonusFields();
    }
});
</script>
@endpush
@endif
@endsection
