<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer's yearly occasion. One row per occasion: event_date keeps the original
 * date and every yearly occurrence is calculated from its month/day. A Feb 29 occasion
 * falls on Feb 28 in non-leap years.
 */
class OccasionReminder extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'event_date',
        'dua_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'immutable_date',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function dua(): BelongsTo
    {
        return $this->belongsTo(Dua::class);
    }

    public function sentNotifications(): HasMany
    {
        return $this->hasMany(OccasionReminderNotification::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Active reminders whose yearly occurrence is on the given day.
     */
    public function scopeOccurringOn(Builder $query, CarbonInterface $date): Builder
    {
        $date = CarbonImmutable::parse($date->toDateString());

        return $query
            ->whereDate('event_date', '<=', $date->toDateString())
            ->whereMonth('event_date', $date->month)
            ->where(function (Builder $query) use ($date): void {
                $query->whereDay('event_date', $date->day);

                if ($date->month === 2 && $date->day === 28 && ! $date->isLeapYear()) {
                    $query->orWhereDay('event_date', 29);
                }
            });
    }

    public function occurrenceInYear(int $year): CarbonImmutable
    {
        $month = $this->event_date->month;
        $day = $this->event_date->day;

        if ($month === 2 && $day === 29 && ! CarbonImmutable::create($year)->isLeapYear()) {
            $day = 28;
        }

        return CarbonImmutable::create($year, $month, $day)->startOfDay();
    }

    /**
     * The next occurrence on or after $today (today itself counts), never before the event itself.
     */
    public function nextOccurrence(?CarbonInterface $today = null): CarbonImmutable
    {
        $today = CarbonImmutable::parse(($today ?? now())->toDateString());
        $year = max($today->year, $this->event_date->year);
        $occurrence = $this->occurrenceInYear($year);

        return $occurrence->lessThan($today) ? $this->occurrenceInYear($year + 1) : $occurrence;
    }
}
