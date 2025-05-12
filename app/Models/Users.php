<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'userId';
    public $timestamps = true;

    protected $fillable = [
        'userId',
        'username',
        'email',
        'passwordHash',
        'name',
        'userRole'
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
