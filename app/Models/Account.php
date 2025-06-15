<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'budget_id',
        'name',
        'type',
        'balance',
        'interest',
        'minimum_payment_monthly'
    ];

    protected $attributes = [
        // Cash, Loan
        'type' => 'cash',
        'balance' => 0,
        'interest' => 0,
        'minimum_payment_monthly' => 0
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:4',
            'interest' => 'decimal:4',
            'minimum_payment_monthly' => 'decimal:4'
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
