<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    public const STATUS_APPROVED = 'approved';
    public const STATUS_REVERSED = 'reversed';

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer (NEFT/RTGS)',
        'upi' => 'UPI',
        'cheque' => 'Cheque',
    ];

    protected $fillable = [
        'voucher_number',
        'expense_date',
        'expense_category_id',
        'cash_account_id',
        'amount',
        'payment_method',
        'paid_to',
        'transaction_reference',
        'status',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'reversed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'cash_account_id');
    }

    public function cashAccount(): BelongsTo
    {
        return $this->account();
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
        return $this->status === self::STATUS_REVERSED;
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->isReversed();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeReversed($query)
    {
        return $query->where('status', self::STATUS_REVERSED);
    }

    public function scopeMethod($query, ?string $method)
    {
        if (empty($method)) {
            return $query;
        }

        if ($method === 'bank') {
            return $query->whereIn('payment_method', ['bank_transfer', 'cheque']);
        }

        return $query->where('payment_method', $method);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $like = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('voucher_number', 'like', $like)
                ->orWhere('paid_to', 'like', $like)
                ->orWhere('transaction_reference', 'like', $like)
                ->orWhere('remarks', 'like', $like)
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like))
                ->orWhereHas('account', fn ($a) => $a->where('name', 'like', $like));
        });
    }

    public function getMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
