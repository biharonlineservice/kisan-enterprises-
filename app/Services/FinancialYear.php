<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Indian financial year helper (default April–March, start month configurable in Settings).
 */
class FinancialYear
{
    public static function startMonth(): int
    {
        $month = (int) setting('locale.financial_year_start_month', 4);

        return ($month >= 1 && $month <= 12) ? $month : 4;
    }

    /** Label such as "2026-27" for the financial year containing $date. */
    public static function label(?CarbonInterface $date = null): string
    {
        $start = self::start($date);
        if (self::startMonth() === 1) {
            return (string) $start->year;
        }

        return $start->year.'-'.substr((string) ($start->year + 1), -2);
    }

    public static function start(?CarbonInterface $date = null): Carbon
    {
        $date = Carbon::instance($date ?? now());
        $month = self::startMonth();
        $year = $date->month >= $month ? $date->year : $date->year - 1;

        return Carbon::create($year, $month, 1)->startOfDay();
    }

    public static function end(?CarbonInterface $date = null): Carbon
    {
        return self::start($date)->addYear()->subDay()->endOfDay();
    }
}
