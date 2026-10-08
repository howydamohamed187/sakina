<?php

namespace App\Console\Commands;

use App\Models\OccasionReminder;
use App\Models\OccasionReminderNotification;
use App\Notifications\OccasionReminderNotification as OccasionReminderMessage;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Daily: notifies customers whose active occasions fall today. Each (reminder, occurrence date)
 * is claimed in occasion_reminder_notifications (unique) before sending, so running the
 * command several times on the same day never sends the same reminder twice.
 */
class SendOccasionReminders extends Command
{
    protected $signature = 'occasion-reminders:send {--date= : Occurrence date (Y-m-d), defaults to today}';

    protected $description = 'Send yearly occasion reminders that fall on the given day';

    public function handle(): int
    {
        $date = CarbonImmutable::parse($this->option('date') ?: now()->toDateString())->startOfDay();
        $sent = 0;
        $skipped = 0;

        OccasionReminder::query()
            ->active()
            ->occurringOn($date)
            ->whereHas('customer', fn ($query) => $query->where('status', 'active'))
            ->with(['customer', 'dua:id,title'])
            ->chunkById(200, function ($reminders) use ($date, &$sent, &$skipped): void {
                foreach ($reminders as $reminder) {
                    $key = [
                        'occasion_reminder_id' => $reminder->id,
                        'occurrence_date' => $date->toDateString(),
                    ];

                    $claimed = OccasionReminderNotification::query()->insertOrIgnore([
                        ...$key,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($claimed === 0) {
                        $skipped++;

                        continue;
                    }

                    try {
                        $reminder->customer->notify(new OccasionReminderMessage($reminder, $date));
                        OccasionReminderNotification::query()->where($key)->update(['sent_at' => now(), 'updated_at' => now()]);
                        $sent++;
                    } catch (Throwable $e) {
                        OccasionReminderNotification::query()->where($key)->delete();
                        Log::error('Occasion reminder failed.', ['reminder_id' => $reminder->id, 'error' => $e->getMessage()]);
                    }
                }
            });

        $this->info("Occasion reminders for {$date->toDateString()}: {$sent} sent, {$skipped} already sent.");

        return self::SUCCESS;
    }
}
