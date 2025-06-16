<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChat extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'content',
        'category_ids',
        'transaction_ids',
        'account_ids',
        'budget_id',
        'title',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    protected function casts(): array
    {
        return [
            'category_ids' => 'json',
            'transaction_ids' => 'json',
            'account_ids' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class);
    }
}
