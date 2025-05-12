<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportReport extends Model
{
    protected $primaryKey = 'reportId';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'reportId',
        'userId',
        'budgetId',
        'generatedDate',
        'reportLink'
    ];

    protected $casts = [
        'generatedDate' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budgetId');
    }
}
