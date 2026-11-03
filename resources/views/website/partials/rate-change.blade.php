{{--
    Price change badge for a crop rate.
    Expects: $trend (array from CropPriceTrendService), $isHindi (bool), optional $size ('sm'|'md')
--}}
@php
    $size = $size ?? 'sm';
    $fmtAmt = function ($v) {
        $v = abs((float) $v);
        return setting('locale.currency_symbol', '₹') . indian_number($v, fmod($v, 1) ? 2 : 0);
    };
@endphp
@switch($trend['direction'] ?? 'new')
    @case('up')
        <span class="rate-change rate-up rate-{{ $size }}" title="{{ $isHindi ? 'पिछले भाव से बढ़ा' : 'Up from previous price' }}">
            <i class="bi bi-caret-up-fill"></i>{{ $fmtAmt($trend['change']) }}
            @if($trend['change_pct'] !== null)<span class="rate-pct">({{ number_format(abs($trend['change_pct']), 1) }}%)</span>@endif
        </span>
        @break
    @case('down')
        <span class="rate-change rate-down rate-{{ $size }}" title="{{ $isHindi ? 'पिछले भाव से घटा' : 'Down from previous price' }}">
            <i class="bi bi-caret-down-fill"></i>{{ $fmtAmt($trend['change']) }}
            @if($trend['change_pct'] !== null)<span class="rate-pct">({{ number_format(abs($trend['change_pct']), 1) }}%)</span>@endif
        </span>
        @break
    @case('same')
        <span class="rate-change rate-same rate-{{ $size }}">
            <i class="bi bi-dash"></i>{{ $isHindi ? 'स्थिर' : 'No change' }}
        </span>
        @break
    @default
        <span class="rate-change rate-new rate-{{ $size }}">
            <i class="bi bi-stars"></i>{{ $isHindi ? 'नया भाव' : 'New rate' }}
        </span>
@endswitch

