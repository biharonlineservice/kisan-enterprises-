<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReceipt extends Model
{
    use HasFactory;

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer (NEFT/RTGS/IMPS)',
        'upi' => 'UPI',
        'cheque' => 'Cheque / Demand Draft',
    ];

    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'customer_id',
        'sales_invoice_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'bank_name',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'reversed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
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
        return ! is_null($this->reversed_at);
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->isReversed();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('reversed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNull('reversed_at');
    }

    public function scopeReversed($query)
    {
        return $query->whereNotNull('reversed_at');
    }

    public function scopeMethod($query, ?string $method)
    {
        if (empty($method)) {
            return $query;
        }

        if ($method === 'bank') {
            return $query->whereIn('payment_method', ['bank_transfer', 'neft_rtgs', 'cheque']);
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
            $q->where('receipt_number', 'like', $like)
                ->orWhere('transaction_reference', 'like', $like)
                ->orWhere('bank_name', 'like', $like)
                ->orWhere('notes', 'like', $like)
                ->orWhereHas('customer', function ($c) use ($like) {
                    $c->where('name', 'like', $like)
                        ->orWhere('customer_id', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('mobile', 'like', $like)
                        ->orWhere('city', 'like', $like);
                })
                ->orWhereHas('invoice', function ($i) use ($like) {
                    $i->where('invoice_number', 'like', $like)
                        ->orWhere('vehicle_number', 'like', $like);
                });
        });
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
