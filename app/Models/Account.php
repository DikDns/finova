<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $primaryKey = 'accountId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'accountId',
        'budgetId',
        'accountName',
        'balance'
    ];

    protected $casts = [
        'balance' => 'decimal:2'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budgetId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'accountId');
    }
}
