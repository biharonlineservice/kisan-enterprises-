@extends('layouts.app')

@section('title', __('website_content.title'))

@section('content')
{{-- 1. Executive Botanical Command Banner --}}
<div class="erp-hero-banner mb-4">
    <div class="position-relative z-1 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
        <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="erp-hero-chip" style="white-space: nowrap;">
                    <i class="bi bi-broadcast text-success"></i>
                    <strong>{{ __('website_content.banner_live_rates', ['count' => $metrics['ratesLive']]) }}</strong>
                </span>
                <span class="erp-hero-chip d-none d-sm-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-flower1"></i>
                    {{ __('website_content.banner_commodities', ['count' => $metrics['totalCrops']]) }}
                </span>
                <span class="erp-hero-chip d-none d-md-inline-flex" style="white-space: nowrap;">
                    <i class="bi bi-inbox"></i>
                    {{ __('website_content.banner_inquiries', ['count' => $metrics['totalInquiries']]) }}
                </span>
                <span class="erp-hero-chip" style="background: rgba(16, 185, 129, .25); border-color: rgba(16, 185, 129, .5); color: #a7f3d0; white-space: nowrap;">
                    <i class="bi bi-bell-fill text-warning"></i>
                    <strong>{{ __('website_content.banner_new', ['count' => $metrics['newInquiries']]) }}</strong>
                </span>
            </div>

            <h2 class="h4 fw-bold mb-1 text-white">{{ __('website_content.title') }}</h2>
            <p class="mb-0 small text-white-50" style="max-width: 760px;">
                {{ __('website_content.subtitle') }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('home') }}" target="_blank" class="erp-hero-btn" title="{{ __('website_content.btn_view_site') }}">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>{{ __('website_content.btn_view_site') }}</span>
            </a>
            <a href="{{ route('website.admin.inquiries') }}" class="erp-hero-btn" title="{{ __('website_content.btn_inquiries') }}">
                <i class="bi bi-chat-left-dots"></i>
                <span>{{ __('website_content.btn_inquiries') }}</span>
            </a>
            <a href="{{ route('crops.index') }}" class="erp-hero-btn" title="{{ __('website_content.btn_crops') }}">
                <i class="bi bi-flower1"></i>
                <span>{{ __('website_content.btn_crops') }}</span>
            </a>
            @if(\Illuminate\Support\Facades\Route::has('settings.edit'))
                <a href="{{ route('settings.edit') }}" class="erp-hero-btn" title="{{ __('website_content.btn_settings') }}">
                    <i class="bi bi-gear"></i>
                    <span>{{ __('website_content.btn_settings') }}</span>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- 2. Business Intelligence KPI Metric Cards --}}
<div class="row g-2 g-md-3 mb-4">
    {{-- Card 1: Hero Live Rates --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('website.admin.index', ['tab' => 'rates']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #21864B;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('website_content.kpi_live_rates') }}</span>
                    <span class="kpi-badge-icon" style="background: #E8F3EC; color: #21864B;">
                        <i class="bi bi-broadcast"></i>
                    </span>
                </div>
                <div class="kpi-value text-success text-truncate">{{ number_format($metrics['ratesLive']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('website_content.kpi_live_rates_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 2: Active Commodities --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('crops.index') }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #0284C7;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('website_content.kpi_active_crops') }}</span>
                    <span class="kpi-badge-icon" style="background: #E0F2FE; color: #0284C7;">
                        <i class="bi bi-flower1"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #0284C7;">{{ number_format($metrics['totalCrops']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('website_content.kpi_active_crops_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 3: Total Inquiries --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('website.admin.index', ['tab' => 'inquiries']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #F4B740;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('website_content.kpi_total_inquiries') }}</span>
                    <span class="kpi-badge-icon" style="background: #FEF6E4; color: #B45309;">
                        <i class="bi bi-inbox-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #B45309;">{{ number_format($metrics['totalInquiries']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('website_content.kpi_total_inquiries_sub') }}
                </div>
            </div>
        </a>
    </div>

    {{-- Card 4: Unread Leads --}}
    <div class="col-6 col-xl-3">
        <a href="{{ route('website.admin.index', ['tab' => 'inquiries']) }}" class="text-decoration-none d-block h-100">
            <div class="erp-kpi-card hover-card" style="--kpi-accent: #7E22CE;">
                <div class="kpi-top">
                    <span class="kpi-label text-truncate">{{ __('website_content.kpi_new_inquiries') }}</span>
                    <span class="kpi-badge-icon" style="background: #F3E8FF; color: #7E22CE;">
                        <i class="bi bi-envelope-exclamation-fill"></i>
                    </span>
                </div>
                <div class="kpi-value text-truncate" style="color: #7E22CE;">{{ number_format($metrics['newInquiries']) }}</div>
                <div class="kpi-sub text-truncate">
                    {{ __('website_content.kpi_new_inquiries_sub') }}
                </div>
            </div>
        </a>
    </div>
</div>

{{-- 3. Navigation Tabs Bar --}}
<div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-2 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
        <ul class="nav erp-tab-pills gap-1 flex-nowrap overflow-x-auto pb-1 pb-xl-0 flex-grow-1" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none; min-width: 0;">
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'rates' ? 'active' : '' }}" 
                   href="{{ route('website.admin.index', ['tab' => 'rates']) }}">
                    <i class="bi bi-broadcast"></i>
                    <span>{{ __('website_content.tab_rates') }}</span>
                    <span class="badge {{ $tab === 'rates' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                        {{ $metrics['ratesLive'] }}
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'cms' ? 'active' : '' }}" 
                   href="{{ route('website.admin.index', ['tab' => 'cms']) }}">
                    <i class="bi bi-pencil-square"></i>
                    <span>{{ __('website_content.tab_cms') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'seo' ? 'active' : '' }}" 
                   href="{{ route('website.admin.index', ['tab' => 'seo']) }}">
                    <i class="bi bi-google"></i>
                    <span>{{ __('website_content.tab_seo') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'inquiries' ? 'active' : '' }}" 
                   href="{{ route('website.admin.index', ['tab' => 'inquiries']) }}">
                    <i class="bi bi-chat-left-dots-fill"></i>
                    <span>{{ __('website_content.tab_inquiries') }}</span>
                    @if($metrics['newInquiries'] > 0)
                        <span class="badge bg-danger text-white rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['newInquiries'] }}
                        </span>
                    @else
                        <span class="badge {{ $tab === 'inquiries' ? 'bg-success text-white' : 'bg-light text-dark border' }} rounded-pill ms-1" style="font-size: .7rem;">
                            {{ $metrics['totalInquiries'] }}
                        </span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-nowrap d-inline-flex align-items-center gap-1 {{ $tab === 'all' ? 'active' : '' }}" 
                   href="{{ route('website.admin.index', ['tab' => 'all']) }}">
                    <i class="bi bi-layers-fill"></i>
                    <span>{{ __('website_content.tab_all') }}</span>
                </a>
            </li>
        </ul>

        <div class="d-none d-xl-flex align-items-center gap-2 flex-shrink-0 text-nowrap pe-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i>SSL Encrypted
            </span>
            <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                <i class="bi bi-activity me-1"></i>Live Mandi Feed
            </span>
        </div>
    </div>
</div>

{{-- 4. Hero Live Crop Purchase Rates Module --}}
@if($tab === 'rates' || $tab === 'all')
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success p-2 rounded-2">
                        <i class="bi bi-broadcast fs-6"></i>
                    </span>
                    <span>{{ __('website_content.rates_title') }}</span>
                </h6>
                <small class="text-muted d-block mt-1">{{ __('website_content.rates_desc') }}</small>
            </div>
            <a href="{{ route('crops.index') }}" class="btn btn-sm btn-outline-success fw-semibold">
                <i class="bi bi-flower1 me-1"></i> {{ __('website_content.btn_crops') }}
            </a>
        </div>

        <div class="card-body p-0">
            @if($crops->isEmpty())
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-flower1 text-muted fs-1 d-block mb-2"></i>
                    <p class="mb-2 fw-semibold">No active crops registered in the catalogue.</p>
                    <a href="{{ route('crops.create') }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Add Crop in Master
                    </a>
                </div>
            @else
                <form action="{{ route('website.admin.crop-rates.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="min-width: 220px;">{{ __('website_content.col_crop') }}</th>
                                    <th style="min-width: 170px;">{{ __('website_content.col_base_rate') }}</th>
                                    <th style="min-width: 170px;">{{ __('website_content.col_max_rate') }}</th>
                                    <th style="min-width: 220px;">{{ __('website_content.col_note') }}</th>
                                    <th class="text-center" style="width: 130px;">{{ __('website_content.col_show') }}</th>
                                    <th class="pe-4 text-end" style="min-width: 190px;">{{ __('website_content.col_trend') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($crops as $crop)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                                    <i class="bi bi-flower1"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $crop->name }}</div>
                                                    <div class="small text-muted">
                                                        <span class="badge bg-light text-secondary border font-monospace">{{ $crop->code }}</span>
                                                        &bull; per {{ ucfirst($crop->unit) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light fw-bold text-muted">₹</span>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       name="rates[{{ $crop->id }}][current_purchase_rate]"
                                                       value="{{ old("rates.{$crop->id}.current_purchase_rate", $crop->current_purchase_rate) }}"
                                                       class="form-control font-monospace fw-semibold @error("rates.{$crop->id}.current_purchase_rate") is-invalid @enderror"
                                                       placeholder="e.g. 2300">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light fw-bold text-muted">₹</span>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       name="rates[{{ $crop->id }}][max_purchase_rate]"
                                                       value="{{ old("rates.{$crop->id}.max_purchase_rate", $crop->max_purchase_rate) }}"
                                                       class="form-control font-monospace fw-semibold @error("rates.{$crop->id}.max_purchase_rate") is-invalid @enderror"
                                                       placeholder="Optional max">
                                            </div>
                                            @error("rates.{$crop->id}.max_purchase_rate")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="rates[{{ $crop->id }}][rate_note]"
                                                   value="{{ old("rates.{$crop->id}.rate_note", $crop->rate_note) }}"
                                                   class="form-control form-control-sm"
                                                   placeholder="e.g. FAQ / Grade A, Moisture <= 14%">
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block mb-0">
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       name="rates[{{ $crop->id }}][show_rate_on_website]"
                                                       value="1"
                                                       role="switch"
                                                       style="cursor: pointer;"
                                                       {{ old("rates.{$crop->id}.show_rate_on_website", $crop->show_rate_on_website) ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            @if(isset($priceTrends) && $priceTrends->has($crop->id))
                                                @php $admTrend = $priceTrends[$crop->id]; @endphp
                                                @if($admTrend['previous'] !== null)
                                                    <div class="mb-1">
                                                        @if($admTrend['direction'] === 'up')
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold font-monospace">
                                                                ▲ ₹{{ indian_number(abs($admTrend['change']), 0) }} (Prev: ₹{{ indian_number($admTrend['previous'], 0) }})
                                                            </span>
                                                        @elseif($admTrend['direction'] === 'down')
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold font-monospace">
                                                                ▼ ₹{{ indian_number(abs($admTrend['change']), 0) }} (Prev: ₹{{ indian_number($admTrend['previous'], 0) }})
                                                            </span>
                                                        @else
                                                            <span class="badge bg-light text-secondary border font-monospace">{{ __('website_content.no_change') }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                            <div class="small text-muted font-monospace">
                                                {{ $crop->rate_updated_at ? fmt_date($crop->rate_updated_at, true) : __('website_content.not_set') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
                        <small class="text-muted d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-success fs-6"></i>
                            <span>{{ __('website_content.tip_rates') }}</span>
                        </small>
                        <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                            <i class="bi bi-broadcast me-1"></i> {{ __('website_content.btn_publish_rates') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

{{-- 5. CMS Content, SEO & Inquiries Modules --}}
@if($tab === 'cms' || $tab === 'seo' || $tab === 'inquiries' || $tab === 'all')
    <div class="row g-4">
        {{-- CMS Content Editor --}}
        @if($tab === 'cms' || $tab === 'all')
            <div class="{{ $tab === 'all' ? 'col-lg-7' : 'col-12' }}">
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-2">
                                <i class="bi bi-pencil-square fs-6"></i>
                            </span>
                            <span>{{ __('website_content.cms_title') }}</span>
                        </h6>
                        <small class="text-muted d-block mt-1">{{ __('website_content.cms_desc') }}</small>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('website.admin.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="return_tab" value="cms">

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-card-heading text-success"></i>
                                    {{ __('website_content.label_hero_headline') }}
                                </label>
                                <input type="text" name="hero_headline" class="form-control" 
                                       value="{{ \App\Models\WebsiteContent::get('hero_headline', 'Empowering Farmers. Supplying Industries. Transparent Grain Trading.') }}">
                                <div class="form-text text-muted">{{ __('website_content.help_hero_headline') }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-text-paragraph text-success"></i>
                                    {{ __('website_content.label_hero_subtitle') }}
                                </label>
                                <textarea name="hero_subtitle" rows="3" class="form-control">{{ \App\Models\WebsiteContent::get('hero_subtitle', 'Direct procurement from verified farmers with computerised weighbridges and prompt payments. Reliable bulk grain supply for rice mills, flour mills, and food processors.') }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-megaphone-fill text-warning"></i>
                                    {{ __('website_content.label_procurement_banner') }}
                                </label>
                                <input type="text" name="procurement_banner" class="form-control" 
                                       value="{{ \App\Models\WebsiteContent::get('procurement_banner', 'Kharif & Rabi Season Procurement Open — Computerized digital weighbridge active.') }}">
                                <div class="form-text text-muted">{{ __('website_content.help_procurement_banner') }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-info-circle text-primary"></i>
                                    {{ __('website_content.label_about_snippet') }}
                                </label>
                                <textarea name="about_snippet" rows="3" class="form-control">{{ \App\Models\WebsiteContent::get('about_snippet', 'Kisan Enterprises bridges the gap between hardworking farmers and industrial grain consumers. With state-of-the-art weighbridges, secure storage godowns, and fair pricing, we ensure every crop is valued transparently.') }}</textarea>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-telephone-fill text-success"></i>
                                        {{ __('website_content.label_helpline_phone') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                        <input type="text" name="helpline_phone" class="form-control font-monospace" 
                                               value="{{ \App\Models\WebsiteContent::get('helpline_phone', setting('business.phone', '+91 98765 43210')) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-envelope-fill text-primary"></i>
                                        {{ __('website_content.label_contact_email') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="contact_email" class="form-control font-monospace" 
                                               value="{{ \App\Models\WebsiteContent::get('contact_email', setting('business.email', 'trade@kisanenterprises.com')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end pt-3 border-top">
                                <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> {{ __('website_content.btn_save_content') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- SEO & Search Console Settings --}}
        @if($tab === 'seo' || $tab === 'all')
            @php
                $seoDefaultTitle = \App\Models\WebsiteContent::get('seo_meta_title', setting('business.name', 'KisanHub') . ' — Live Mandi Crop Rates, Direct Procurement & Bulk Grain Supply');
                $seoDefaultDesc  = \App\Models\WebsiteContent::get('seo_meta_description', 'Direct agricultural crop procurement from verified farmers with digital weighbridge, transparent moisture testing, same-day bank/cash payment, and B2B bulk supply of Wheat, Paddy, Maize, and Mustard across Uttar Pradesh & Bihar.');
                $seoDefaultKeys  = \App\Models\WebsiteContent::get('seo_meta_keywords', 'KisanHub, Kisan Enterprises, Mandi Bhav Today, Crop Purchase Rate, Wheat Price per Quintal, Paddy Rate Today, Maize Mandi Rate, Mustard Price, Farmer Registration, Bulk Grain Supplier UP Bihar');
                $seoRobotsVal    = \App\Models\WebsiteContent::get('seo_robots', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
            @endphp
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success p-2 rounded-2">
                                    <i class="bi bi-google fs-6"></i>
                                </span>
                                <span>SEO, Meta Tags &amp; Search Console Configuration</span>
                            </h6>
                            <small class="text-muted d-block mt-1">Manage Google search appearance, Open Graph (WhatsApp/Facebook) cards, Sitemap, and Search Console verification.</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('website.sitemap') }}" target="_blank" class="btn btn-sm btn-outline-success fw-semibold">
                                <i class="bi bi-diagram-3 me-1"></i> View Sitemap.xml
                            </a>
                            <a href="{{ route('website.robots') }}" target="_blank" class="btn btn-sm btn-outline-secondary fw-semibold">
                                <i class="bi bi-robot me-1"></i> View Robots.txt
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Live Google SERP Preview --}}
                        <div class="p-3 rounded-3 border bg-light mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-white text-dark border fw-semibold">
                                    <i class="bi bi-google text-success me-1"></i> Google Search Result Preview (SERP)
                                </span>
                                <span class="small text-muted">Schema.org JSON-LD + Open Graph Active</span>
                            </div>
                            <div class="bg-white p-3 rounded-2 border shadow-xs">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-success-subtle text-success rounded-circle p-1"><i class="bi bi-globe2"></i></span>
                                    <div class="small text-truncate">
                                        <div class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ setting('business.name', 'KisanHub') }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ url('/') }}</div>
                                    </div>
                                </div>
                                <div id="serpPreviewTitle" class="fw-semibold mb-1" style="color: #1a0dab; font-size: 1.08rem; line-height: 1.3;">
                                    {{ $seoDefaultTitle }}
                                </div>
                                <div id="serpPreviewDesc" class="text-secondary" style="font-size: 0.85rem; line-height: 1.45;">
                                    {{ \Illuminate\Support\Str::limit($seoDefaultDesc, 160) }}
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('website.admin.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="return_tab" value="seo">

                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-type-h1 text-success"></i>
                                        Default / Homepage SEO Meta Title
                                    </label>
                                    <input type="text"
                                           name="seo_meta_title"
                                           id="seoMetaTitleInput"
                                           maxlength="255"
                                           class="form-control"
                                           value="{{ $seoDefaultTitle }}"
                                           oninput="document.getElementById('serpPreviewTitle').textContent = this.value || 'KisanHub'">
                                    <div class="form-text text-muted">Recommended: 50–65 characters. Include brand name, primary service (Mandi Rates / Grain Trading), and region.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-robot text-primary"></i>
                                        Search Crawler Indexing (`robots`)
                                    </label>
                                    <select name="seo_robots" class="form-select">
                                        <option value="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" {{ str_starts_with($seoRobotsVal, 'index') ? 'selected' : '' }}>
                                            Allow Indexing (index, follow) — Recommended
                                        </option>
                                        <option value="noindex, nofollow" {{ str_starts_with($seoRobotsVal, 'noindex') ? 'selected' : '' }}>
                                            Discourage Search Engines (noindex, nofollow)
                                        </option>
                                    </select>
                                    <div class="form-text text-muted">Controls whether Google &amp; Bing index public pages.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-card-text text-success"></i>
                                        Default / Homepage Meta Description
                                    </label>
                                    <textarea name="seo_meta_description"
                                              id="seoMetaDescInput"
                                              rows="3"
                                              maxlength="500"
                                              class="form-control"
                                              oninput="document.getElementById('serpPreviewDesc').textContent = this.value">{{ $seoDefaultDesc }}</textarea>
                                    <div class="form-text text-muted">Recommended: 140–165 characters. Displayed in Google Search snippets and WhatsApp link previews.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-tags-fill text-warning"></i>
                                        Target SEO Keywords (Comma Separated)
                                    </label>
                                    <textarea name="seo_meta_keywords" rows="2" maxlength="500" class="form-control">{{ $seoDefaultKeys }}</textarea>
                                    <div class="form-text text-muted">Include local agricultural keywords in English &amp; Hindi/Hinglish (e.g., Mandi Bhav Today, Gehu Dhan Makka Sarson Rate, Kisan Registration).</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-patch-check-fill text-success"></i>
                                        Google Search Console Verification
                                    </label>
                                    <input type="text"
                                           name="seo_google_verification"
                                           class="form-control font-monospace"
                                           placeholder="e.g. google-site-verification token"
                                           value="{{ \App\Models\WebsiteContent::get('seo_google_verification', '') }}">
                                    <div class="form-text text-muted">Paste only the `content` code from `<meta name="google-site-verification" content="...">`.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-microsoft text-primary"></i>
                                        Bing Webmaster Verification (`msvalidate.01`)
                                    </label>
                                    <input type="text"
                                           name="seo_bing_verification"
                                           class="form-control font-monospace"
                                           placeholder="e.g. 32-char Bing token"
                                           value="{{ \App\Models\WebsiteContent::get('seo_bing_verification', '') }}">
                                    <div class="form-text text-muted">Optional verification token for Microsoft Bing Webmaster Tools.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                        <i class="bi bi-graph-up-arrow text-danger"></i>
                                        Google Analytics 4 (GA4) Measurement ID
                                    </label>
                                    <input type="text"
                                           name="seo_ga_measurement_id"
                                           class="form-control font-monospace"
                                           placeholder="e.g. G-XXXXXXXXXX"
                                           value="{{ \App\Models\WebsiteContent::get('seo_ga_measurement_id', '') }}">
                                    <div class="form-text text-muted">Automatically injects Google Tag (`gtag.js`) across all public pages when filled.</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 mt-4 border-top">
                                <div class="small text-muted d-flex flex-wrap align-items-center gap-3">
                                    <span><i class="bi bi-check-circle-fill text-success me-1"></i>Canonical URLs Active</span>
                                    <span><i class="bi bi-check-circle-fill text-success me-1"></i>Bilingual `hreflang` (EN/HI)</span>
                                    <span><i class="bi bi-check-circle-fill text-success me-1"></i>JSON-LD `LocalBusiness` &amp; `OfferCatalog`</span>
                                </div>
                                <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                                    <i class="bi bi-check2-circle me-1"></i> Save SEO Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Inquiries Summary --}}
        @if($tab === 'inquiries' || $tab === 'all')
            <div class="{{ $tab === 'all' ? 'col-lg-5' : 'col-12' }}">
                <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-2">
                                    <i class="bi bi-chat-left-dots-fill fs-6"></i>
                                </span>
                                <span>{{ __('website_content.inquiries_title') }}</span>
                            </h6>
                        </div>
                        <a href="{{ route('website.admin.inquiries') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                            {{ __('website_content.btn_view_all_inquiries') }} &rarr;
                        </a>
                    </div>

                    <div class="card-body p-0">
                        @if($inquiries->isEmpty())
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <p class="mb-0 fw-semibold">{{ __('website_content.no_inquiries') }}</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($inquiries as $inquiry)
                                    <div class="list-group-item p-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($inquiry->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $inquiry->name }}</h6>
                                                    <div class="small text-muted font-monospace">
                                                        <i class="bi bi-telephone me-1"></i>{{ $inquiry->phone }}
                                                        @if($inquiry->email)
                                                            &bull; <i class="bi bi-envelope me-1"></i>{{ $inquiry->email }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                @if($inquiry->isNew())
                                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1">
                                                        {{ __('website_content.badge_new') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                        {{ __('website_content.badge_responded') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <p class="small text-secondary mb-2 bg-light p-2 rounded-2 border">
                                            {{ Str::limit($inquiry->message, 120) }}
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <span class="badge bg-light text-dark border text-capitalize">
                                                {{ str_replace('_', ' ', $inquiry->inquiry_type) }}
                                            </span>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="font-monospace">{{ $inquiry->created_at->diffForHumans() }}</span>
                                                <form action="{{ route('website.admin.inquiries.toggle', $inquiry) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none small text-primary" title="Toggle status">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 bg-success-subtle rounded-3">
                    <div class="card-body p-3 small text-dark d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-success fs-5 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Master Data Auto-Sync:</strong>
                            <p class="mb-0 mt-1 text-secondary">
                                {{ __('website_content.sync_notice') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
@endsection
