<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $primaryKey = 'subscriptionId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subscriptionId',
        'userId',
        'invoice',
        'paymentMethod',
        'startDate',
        'endDate',
        'createdAt',
        'updatedAt'
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
