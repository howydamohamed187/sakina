<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDailyMessageQuota extends Model
{
    protected $fillable = [
        'customer_id',
        'usage_date',
        'reserved',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'reserved' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
