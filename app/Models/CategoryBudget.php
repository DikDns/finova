<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryBudget extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'monthly_budget_id',
        'category_id',
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

    public function monthlyBudget(): BelongsTo
    {
        return $this->belongsTo(MonthlyBudget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
