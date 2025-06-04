<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryBudget extends Model
{
    protected $primaryKey = 'categoryBudgetId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'categoryBudgetId',
        'monthBudgetId',
        'categoryId',
        'categoryMonth',
        'categoryBudgetAssigned',
        'categoryBudgetActivity',
        'categoryBudgetAvailable'
    ];

    protected $casts = [
        'categoryMonth' => 'datetime',
        'categoryBudgetAssigned' => 'decimal:2',
        'categoryBudgetActivity' => 'decimal:2',
        'categoryBudgetAvailable' => 'decimal:2'
    ];

    public function monthlyBudget()
    {
        return $this->belongsTo(MonthlyBudget::class, 'monthBudgetId');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }
}
