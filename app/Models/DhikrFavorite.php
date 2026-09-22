<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DhikrFavorite extends Model
{
    protected $fillable = [
        'dhikr_id',
        'customer_id',
    ];

    public function dhikr(): BelongsTo
    {
        return $this->belongsTo(Dhikr::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
