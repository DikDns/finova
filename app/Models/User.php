<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

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
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

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

    public function subscriptions()
    {   
        return $this->hasMany(Subscription::class, 'userId');
    }

    public function aichats()
    {
        return $this->hasMany(AiChat::class, 'userId');
    }
}
?>