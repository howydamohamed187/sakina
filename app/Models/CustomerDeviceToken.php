<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDeviceToken extends Model
{
    public const PLATFORMS = ['android', 'ios'];

    protected $fillable = [
        'customer_id',
        'personal_access_token_id',
        'token',
        'platform',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
