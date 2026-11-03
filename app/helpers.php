<?php

use App\Services\SettingsService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('setting')) {
    /** Read a business setting (DB value, falling back to config/kisan.php default). */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('indian_number')) {
    /**
     * Format a decimal string/number with Indian digit grouping (12,34,567.89).
     * Works on strings so large decimals are not converted through float.
     */
    function indian_number(string|int|float|null $value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            $value = '0';
        }
        $value = is_string($value) ? $value : number_format((float) $value, $decimals, '.', '');
        if (function_exists('bcadd')) {
            // Normalise/round to the requested scale (half-up).
            $value = bc_round($value, $decimals);
        } else {
            $value = number_format((float) $value, $decimals, '.', '');
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');
        [$int, $frac] = array_pad(explode('.', $value, 2), 2, '');

        $last3 = substr($int, -3);
        $rest = substr($int, 0, -3);
        if ($rest !== '' && $rest !== false) {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $int = $rest.','.$last3;
        }

        $out = $int.($decimals > 0 ? '.'.str_pad($frac, $decimals, '0') : '');

        return ($negative && trim($out, '0.,') !== '' ? '-' : '').$out;
    }
}

if (! function_exists('bc_round')) {
    /** Round a decimal string half-up (away from zero) to $scale places using bcmath. */
    function bc_round(string|int|float|null $value, int $scale = 2): string
    {
        $str = ($value === null || trim((string) $value) === '') ? '0' : trim((string) $value);
        $offset = '0.'.str_repeat('0', $scale).'5';
        $result = str_starts_with($str, '-')
            ? bcsub($str, $offset, $scale)
            : bcadd($str, $offset, $scale);

        return $result === '-0'.($scale ? '.'.str_repeat('0', $scale) : '') ? bcadd('0', '0', $scale) : $result;
    }
}

if (! function_exists('money')) {
    /** Format an amount in INR, e.g. ₹12,34,567.50 */
    function money(string|int|float|null $amount, bool $symbol = true): string
    {
        $formatted = indian_number($amount, 2);
        if (! $symbol) {
            return $formatted;
        }
        $sym = setting('locale.currency_symbol', '₹');

        return str_starts_with($formatted, '-') ? '-'.$sym.substr($formatted, 1) : $sym.$formatted;
    }
}

if (! function_exists('fmt_date')) {
    /** Format a date using the business date format setting. */
    function fmt_date(CarbonInterface|string|null $date, bool $withTime = false): string
    {
        if (empty($date)) {
            return '—';
        }
        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $format = setting('locale.date_format', 'd-m-Y');

        return $date->timezone(config('app.timezone'))->format($withTime ? $format.' h:i A' : $format);
    }
}

if (! function_exists('fmt_datetime')) {
    /** Format a datetime using the business date format and time. */
    function fmt_datetime(CarbonInterface|string|null $date): string
    {
        return fmt_date($date, true);
    }
}

if (! function_exists('indian_fy')) {
    /** Return current or given date's Indian financial year label (e.g. 2026-27). */
    function indian_fy(CarbonInterface|string|null $date = null): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return \App\Services\FinancialYear::label($date);
    }
}

