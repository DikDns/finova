<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyBudget extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'budget_id',
        'month',
        'total_balance',
        'total_assigned',
        'total_activity',
        'total_available'
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'total_balance' => 'decimal:4',
            'total_assigned' => 'decimal:4',
            'total_activity' => 'decimal:4',
            'total_available' => 'decimal:4',
        ];
    }

    public function month(): Attribute
    {
        // Get only the month and year from the date in the database and format it to "Month Year"
        return Attribute::make(
            get: function ($value) {
                return date('F Y', strtotime($value));
            },
            set: function ($value) {
                return date('Y-m-01', strtotime($value));
            }
        );
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function categoryBudgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class);
    }
}
