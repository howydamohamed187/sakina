<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuaFavorite extends Model
{
    protected $fillable = [
        'dua_id',
        'customer_id',
    ];

    public function dua(): BelongsTo
    {
        return $this->belongsTo(Dua::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
