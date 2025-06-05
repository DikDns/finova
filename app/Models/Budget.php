<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    protected $attributes = [
        'amount' => 0,
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

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function categoryGroups()
    {
        return $this->hasMany(CategoryGroup::class);
    }

    public function monthlyBudgets()
    {
        return $this->hasMany(MonthlyBudget::class);
    }

    public function exportReports()
    {
        return $this->hasMany(ExportReport::class);
    }
}
