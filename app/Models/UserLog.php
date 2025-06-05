<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasUuids;

    protected $primaryKey = 'logId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'logId',
        'userId',
        'username',
        'email',
        'name',
        'userRole',
        'createdAt'
    ];

    protected $hidden = [
        'passwordHash'
    ];

    protected $casts = [
        'createdAt' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
