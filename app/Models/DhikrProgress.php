<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DhikrProgress extends Model
{
    protected $table = 'user_dhikr_progress';

    protected $fillable = [
        'customer_id',
        'dhikr_id',
        'current_count',
        'total_count',
        'completed_cycles',
        'last_counted_at',
    ];

    protected function casts(): array
    {
        return [
            'current_count' => 'integer',
            'total_count' => 'integer',
            'completed_cycles' => 'integer',
            'last_counted_at' => 'datetime',
        ];
    }

    public function dhikr(): BelongsTo
    {
        return $this->belongsTo(Dhikr::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
