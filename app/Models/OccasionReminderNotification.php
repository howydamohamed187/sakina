<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per sent yearly occurrence; unique (occasion_reminder_id, occurrence_date).
 */
class OccasionReminderNotification extends Model
{
    protected $fillable = [
        'occasion_reminder_id',
        'occurrence_date',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'occurrence_date' => 'immutable_date',
            'sent_at' => 'datetime',
        ];
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(OccasionReminder::class, 'occasion_reminder_id');
    }
}
