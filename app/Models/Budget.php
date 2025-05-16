<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $keyType = 'string';
    protected $primaryKey = 'budgetId';
    public $incrementing = false;

    protected $fillable = [
        'budgetId',
        'userId',
        'budgetName',
        'currencyType',
        'createdAt',
        'lastModified'
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'lastModified' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'budgetId');
    }

    public function categoryGroups()
    {
        return $this->hasMany(CategoryGroup::class, 'budgetId');
    }

    public function monthlyBudgets()
    {
        return $this->hasMany(MonthlyBudget::class, 'budgetId');
    }

    public function exportReports()
    {
        return $this->hasMany(ExportReport::class, 'budgetId');
    }
}
