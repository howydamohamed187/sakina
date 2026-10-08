<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Notifications\PrayerTimeNotification;
use App\Services\PrayerNotificationPreferences;
use App\Services\PrayerTimesService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs every minute: pushes each prayer whose time (at the customer's saved location) fell
 * within the last window_minutes, only when that prayer is enabled in the customer's
 * prayer notification settings. A cache claim per customer/day/prayer prevents repeats.
 */
class SendPrayerNotifications extends Command
{
    protected $signature = 'prayer-notifications:send {--at= : Reference time (ISO 8601), defaults to now}';

    protected $description = 'Push prayer time notifications according to each customer\'s settings';

    public function handle(PrayerTimesService $prayerTimes, PrayerNotificationPreferences $preferences): int
    {
        $now = $this->option('at') ? CarbonImmutable::parse($this->option('at')) : CarbonImmutable::now();
        $window = max(1, (int) config('prayer_times.notifications.window_minutes', 5));
        $sent = 0;
        $timezones = [];

        Customer::query()
            ->where('status', 'active')
            ->where('notifications_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('deviceTokens')
            ->with('prayerNotificationSettings')
            ->chunkById(200, function ($customers) use ($prayerTimes, $preferences, $now, $window, &$sent, &$timezones): void {
                foreach ($customers as $customer) {
                    $enabled = array_filter($preferences->for($customer), fn (array $settings): bool => $settings['enabled']);

                    if ($enabled === []) {
                        continue;
                    }

                    $location = round($customer->latitude, 2).','.round($customer->longitude, 2);
                    $timezone = $timezones[$location] ??= $prayerTimes->resolveTimezone($customer->latitude, $customer->longitude);

                    try {
                        $times = $prayerTimes->getPrayerTimes($customer->latitude, $customer->longitude, $now->setTimezone($timezone), $timezone);
                    } catch (Throwable $e) {
                        Log::warning('Prayer times unavailable for notifications.', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);

                        continue;
                    }

                    foreach ($enabled as $prayer => $settings) {
                        $time = $times[$prayer] ?? null;

                        if (! $time || $time->greaterThan($now) || $time->lessThanOrEqualTo($now->subMinutes($window))) {
                            continue;
                        }

                        $claim = "prayer_notification:{$customer->id}:{$time->toDateString()}:{$prayer}";

                        if (! Cache::add($claim, true, now()->addDay())) {
                            continue;
                        }

                        try {
                            $customer->notify(new PrayerTimeNotification($prayer, $time, $settings));
                            $sent++;
                        } catch (Throwable $e) {
                            Cache::forget($claim);
                            Log::error('Prayer notification failed.', ['customer_id' => $customer->id, 'prayer' => $prayer, 'error' => $e->getMessage()]);
                        }
                    }
                }
            });

        $this->info("Prayer notifications: {$sent} sent.");

        return self::SUCCESS;
    }
}
