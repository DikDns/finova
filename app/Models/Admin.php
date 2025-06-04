<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $primaryKey = 'adminId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'adminId',
        'username',
        'email',
        'name',
    ];

    protected $hidden = [
        'passwordHash'
    ];
}
