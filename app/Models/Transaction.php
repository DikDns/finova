<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasUuids;

    protected $primaryKey = 'transactionId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'transactionId',
        'accountId',
        'categoryId',
        'payee',
        'transactionDate',
        'transactionAmount',
        'transactionMemo'
    ];

    protected $casts = [
        'transactionDate' => 'datetime',
        'transactionAmount' => 'decimal:2'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'accountId');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }
}
