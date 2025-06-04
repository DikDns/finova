<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'categoryId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'categoryId',
        'groupId',
        'categoryName'
    ];

    public function categoryGroup()
    {
        return $this->belongsTo(CategoryGroup::class, 'groupId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'categoryId');
    }

    public function categoryBudgets()
    {
        return $this->hasMany(CategoryBudget::class, 'categoryId');
    }
}
