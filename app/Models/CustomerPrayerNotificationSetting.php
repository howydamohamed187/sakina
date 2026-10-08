<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPrayerNotificationSetting extends Model
{
    protected $fillable = [
        'customer_id',
        'prayer',
        'enabled',
        'sound_enabled',
        'vibration_enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sound_enabled' => 'boolean',
            'vibration_enabled' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
