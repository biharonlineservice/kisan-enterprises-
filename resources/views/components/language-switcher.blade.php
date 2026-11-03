@php
    $currentLocale = app()->getLocale();
@endphp
<div class="dropdown language-switcher d-inline-block">
    <button class="btn btn-sm btn-light border dropdown-toggle d-inline-flex align-items-center gap-1 px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('app.language') }}">
        <i class="bi bi-translate text-success"></i>
        <span class="fw-semibold d-none d-lg-inline">{{ $currentLocale === 'hi' ? 'हिंदी' : 'English' }}</span>
        <span class="fw-semibold d-lg-none text-uppercase fs-xs">{{ $currentLocale }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm py-1" style="min-width: 140px; z-index: 1060;">
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentLocale === 'hi' ? 'active bg-success text-white' : '' }}" href="{{ route('locale.switch', 'hi') }}">
                <span>हिंदी</span>
                @if($currentLocale === 'hi')
                    <i class="bi bi-check-lg ms-2"></i>
                @endif
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $currentLocale === 'en' ? 'active bg-success text-white' : '' }}" href="{{ route('locale.switch', 'en') }}">
                <span>English</span>
                @if($currentLocale === 'en')
                    <i class="bi bi-check-lg ms-2"></i>
                @endif
            </a>
        </li>
    </ul>
</div>
