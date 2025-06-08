<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryBudget extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'monthly_budget_id',
        'category_id',
        'month',
        'assigned',
        'activity',
        'available'
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'assigned' => 'decimal:4',
            'activity' => 'decimal:4',
            'available' => 'decimal:4'
        ];
    }

    public function month(): Attribute
    {
        // Get only the month and year from the date in the database and format it to "Month Year"
        return Attribute::make(
            get: fn($value) => $value->format('F Y'),
            set: fn($value) => \Carbon\Carbon::parse($value)->startOfMonth(),
        );
    }

    public function monthlyBudget(): BelongsTo
    {
        return $this->belongsTo(MonthlyBudget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
