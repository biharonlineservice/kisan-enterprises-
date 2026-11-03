<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePayment extends Model
{
    use HasFactory;

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer (NEFT/RTGS)',
        'upi' => 'UPI',
        'cheque' => 'Cheque',
    ];

    protected $fillable = [
        'voucher_number', 'purchase_id', 'farmer_id', 'payment_date',
        'amount', 'bonus_percentage', 'bonus_amount', 'total_payout',
        'days_delayed', 'bonus_calculation_mode',
        'payment_method', 'transaction_reference', 'notes',
        'created_by', 'reversed_at', 'reversed_by', 'reversal_reason',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'reversed_at' => 'datetime',
        'amount' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'total_payout' => 'decimal:2',
        'days_delayed' => 'integer',
    ];

    public function getEffectivePayoutAttribute(): string
    {
        return bccomp((string) $this->total_payout, '0.00', 2) > 0
            ? (string) $this->total_payout
            : (string) $this->amount;
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->isReversed();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('reversed_at');
    }

    public function scopeReversed($query)
    {
        return $query->whereNotNull('reversed_at');
    }

    public function getMethodNameAttribute(): string
    {
        return self::METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->method_name;
    }
}
