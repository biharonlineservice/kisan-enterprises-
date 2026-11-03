<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Generates sequential document numbers such as "PUR/2026-27/00001".
 *
 * The sequence row is locked with SELECT ... FOR UPDATE, so concurrent requests
 * can never receive the same number. Call inside the same DB transaction that
 * saves the document so a rollback also releases the number.
 */
class DocumentNumber
{
    public const TYPES = [
        'purchase', 'sales_order', 'sales_invoice', 'farmer_payment',
        'customer_receipt', 'expense', 'farmer', 'customer',
        'stock_transfer', 'stock_adjustment',
    ];

    public static function next(string $type, ?CarbonInterface $date = null): string
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Unknown document type [$type].");
        }

        $fy = FinancialYear::label($date);

        return DB::transaction(function () use ($type, $fy) {
            DB::table('document_sequences')->insertOrIgnore([
                'doc_type' => $type,
                'financial_year' => $fy,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('document_sequences')
                ->where('doc_type', $type)
                ->where('financial_year', $fy)
                ->lockForUpdate()
                ->first();

            $number = $row->last_number + 1;

            DB::table('document_sequences')
                ->where('id', $row->id)
                ->update(['last_number' => $number, 'updated_at' => now()]);

            return self::format($type, $fy, $number);
        });
    }

    public static function format(string $type, string $fy, int $number): string
    {
        $prefix = setting("numbering.$type", strtoupper($type));
        $padding = max(3, min(8, (int) setting('numbering.padding', 5)));

        // Master records (farmers/customers) don't reset per year, so omit the FY.
        if (in_array($type, ['farmer', 'customer'], true)) {
            return $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
        }

        return $prefix.'/'.$fy.'/'.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
    }
}
