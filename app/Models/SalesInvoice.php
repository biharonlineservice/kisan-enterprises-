<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_REVERSED = 'reversed';

    public const DELIVERY_IN_TRANSIT = 'in_transit';
    public const DELIVERY_DELIVERED = 'delivered';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_id',
        'sales_order_id',
        'status',
        'subtotal_amount',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'round_off',
        'total_amount',
        'paid_amount',
        'balance_due',
        'vehicle_number',
        'driver_name',
        'transporter_name',
        'eway_bill_number',
        'delivery_destination',
        'delivery_status',
        'delivered_at',
        'delivered_by',
        'received_by',
        'delivery_notes',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'delivered_at' => 'datetime',
        'reversed_at' => 'datetime',
        'subtotal_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(CustomerReceipt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function isDelivered(): bool
    {
        return $this->delivery_status === self::DELIVERY_DELIVERED;
    }

    public function isDeliveryPending(): bool
    {
        return $this->isFinalized() && ! $this->isDelivered();
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->isReversed();
    }

    public function getNetAmountAttribute(): string
    {
        return (string) ($this->attributes['total_amount'] ?? '0.00');
    }

    public function totalQuantityQtl(): string
    {
        $sum = '0.000';
        foreach ($this->items as $item) {
            $sum = bcadd($sum, (string) $item->quantity_qtl, 3);
        }

        return $sum;
    }

    public function totalQuantityKg(): string
    {
        $sum = '0.000';
        foreach ($this->items as $item) {
            $sum = bcadd($sum, (string) $item->quantity_kg, 3);
        }

        return $sum;
    }

    public function challanNumber(): string
    {
        $prefix = setting('numbering.delivery_challan', 'DC');
        $invPrefix = setting('numbering.sales_invoice', 'INV');
        if (str_starts_with($this->invoice_number, $invPrefix)) {
            return $prefix . substr($this->invoice_number, strlen($invPrefix));
        }

        return $prefix . '/' . $this->invoice_number;
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }

    public function scopeReversed($query)
    {
        return $query->where('status', self::STATUS_REVERSED);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_FINALIZED)->where('balance_due', '>', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_FINALIZED)->where('balance_due', '<=', 0);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_FINALIZED)->where('delivery_status', self::DELIVERY_DELIVERED);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', self::STATUS_FINALIZED)->where('delivery_status', '!=', self::DELIVERY_DELIVERED);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $like = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('invoice_number', 'like', $like)
                ->orWhere('vehicle_number', 'like', $like)
                ->orWhere('driver_name', 'like', $like)
                ->orWhere('transporter_name', 'like', $like)
                ->orWhere('eway_bill_number', 'like', $like)
                ->orWhere('delivery_destination', 'like', $like)
                ->orWhereHas('customer', function ($c) use ($like) {
                    $c->where('name', 'like', $like)
                        ->orWhere('customer_id', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('mobile', 'like', $like)
                        ->orWhere('city', 'like', $like);
                });
        });
    }
}
