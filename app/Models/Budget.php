<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasUuids, HasFactory;


    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    protected $attributes = [
        'currency_code' => 'IDR',
    ];

    protected function currencyCode(): Attribute
    {
        return new Attribute(
            set: fn($value) => strtoupper($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function categoryGroups(): HasMany
    {
        return $this->hasMany(CategoryGroup::class);
    }

    public function monthlyBudgets(): HasMany
    {
        return $this->hasMany(MonthlyBudget::class);
    }

    public function exportReports(): HasMany
    {
        return $this->hasMany(ExportReport::class);
    }
}
