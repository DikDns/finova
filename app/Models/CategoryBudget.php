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
        'catBudgetAssigned',
        'catBudgetActivity',
        'catBudgetAvailable'
    ];

    protected $casts = [
        'categoryMonth' => 'datetime',
        'catBudgetAssigned' => 'decimal:2',
        'catBudgetActivity' => 'decimal:2',
        'catBudgetAvailable' => 'decimal:2'
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
