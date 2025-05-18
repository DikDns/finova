<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryGroup extends Model
{
    protected $primaryKey = 'groupId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'groupId',
        'budgetId',
        'groupName'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budgetId');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'groupId');
    }
}
