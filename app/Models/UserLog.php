<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLog extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'username',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values'
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'json',
            'new_values' => 'json'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
