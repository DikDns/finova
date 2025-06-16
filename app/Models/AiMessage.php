<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ai_chat_id',
        'content',
        'role',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    public function aiChat(): BelongsTo
    {
        return $this->belongsTo(AiChat::class);
    }
}