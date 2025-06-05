<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AiChat extends Model
{
    use HasUuids;

    protected $primaryKey = 'aiChatId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'aiChatId',
        'userId',
        'role',
        'content',
        'categoryIds',
        'transactionIds',
        'accountIds',
        'createdAt',
        'updatedAt'
    ];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
