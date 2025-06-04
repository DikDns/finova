<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $primaryKey = 'categoryGroupId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'categoryGroupId',
        'budgetId',
        'categoryGroupName'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budgetId');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'categoryGroupId');
    }
}
