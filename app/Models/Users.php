<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'userId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'userId',
        'username',
        'email',
        'name',
        'userRole'
    ];

    protected $hidden = [
        'passwordHash'
    ];

    // Relationships
    public function budgets()
    {
        return $this->hasMany(Budget::class, 'userId');
    }

    public function userLogs()
    {
        return $this->hasMany(UserLog::class, 'userId');
    }

    public function exportReports()
    {
        return $this->hasMany(ExportReport::class, 'userId');
    }
}