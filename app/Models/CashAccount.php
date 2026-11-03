<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAccount extends Model
{
    use HasFactory;

    public const TYPE_CASH = 'cash';
    public const TYPE_BANK = 'bank';

    protected $fillable = [
        'name',
        'account_type',
        'account_number',
        'bank_name',
        'ifsc_code',
        'opening_balance',
        'current_balance',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    public function isCash(): bool
    {
        return $this->account_type === self::TYPE_CASH;
    }

    public function isBank(): bool
    {
        return $this->account_type === self::TYPE_BANK;
    }

    public function scopeCash($query)
    {
        return $query->where('account_type', self::TYPE_CASH);
    }

    public function scopeBank($query)
    {
        return $query->where('account_type', self::TYPE_BANK);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('bank_name', 'like', "%{$term}%")
              ->orWhere('account_number', 'like', "%{$term}%")
              ->orWhere('ifsc_code', 'like', "%{$term}%");
        });
    }
}
