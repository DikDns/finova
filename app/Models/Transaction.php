<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'payee',
        'amount',
        'date',
        'memo',
        'type',
        'category_id',
        'account_id',
        'budget_id',
    ];

    protected $casts = [
        'id' => 'string',
        'amount' => 'decimal:4',
        'date' => 'date',
        'type' => 'string',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
}
