<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyBudget extends Model
{
    protected $primaryKey = 'monthBudgetId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'monthBudgetId',
        'budgetId',
        'budgetMonth',
        'monthBudgetIncome',
        'monthBudgetAssigned',
        'monthBudgetActivity',
        'monthBudgetAvailable'
    ];

    protected $casts = [
        'budgetMonth' => 'datetime',
        'monthBudgetIncome' => 'decimal:2',
        'monthBudgetAssigned' => 'decimal:2',
        'monthBudgetActivity' => 'decimal:2',
        'monthBudgetAvailable' => 'decimal:2'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budgetId');
    }

    public function categoryBudgets()
    {
        return $this->hasMany(CategoryBudget::class, 'monthBudgetId');
    }
}
