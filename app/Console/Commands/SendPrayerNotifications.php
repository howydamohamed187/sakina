<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Notifications\AdhanNotification;
use App\Notifications\CustomerPushNotification;
use App\Notifications\PrayerApproachingNotification;
use App\Services\PrayerNotificationPreferences;
use App\Services\PrayerTimesService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs every minute. For each prayer enabled in the customer's settings (at their saved
 * location) it sends the approaching alert before_minutes earlier and the adhan alert at the
 * prayer time, each within window_minutes. A cache claim per customer/day/prayer/type prevents repeats.
 */
class SendPrayerNotifications extends Command
{
    protected $signature = 'prayer-notifications:send {--at= : Reference time (ISO 8601), defaults to now}';

    protected $description = 'Push prayer time notifications according to each customer\'s settings';

    public function handle(PrayerTimesService $prayerTimes, PrayerNotificationPreferences $preferences): int
    {
        $now = $this->option('at') ? CarbonImmutable::parse($this->option('at')) : CarbonImmutable::now();
        $window = max(1, (int) config('prayer_times.notifications.window_minutes', 5));
        $before = max(0, (int) config('prayer_times.notifications.before_minutes', 15));
        $sent = 0;
        $timezones = [];

        Customer::query()
            ->where('status', 'active')
            ->where('notifications_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('deviceTokens')
            ->with('prayerNotificationSettings')
            ->chunkById(200, function ($customers) use ($prayerTimes, $preferences, $now, $window, $before, &$sent, &$timezones): void {
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

                        if (! $time) {
                            continue;
                        }

                        if ($before > 0 && $this->isDue($time->subMinutes($before), $now, $window)) {
                            $sent += $this->sendOnce($customer, "prayer_approaching:{$customer->id}:{$time->toDateString()}:{$prayer}", new PrayerApproachingNotification($prayer, $time, $before, $settings));
                        }

                        if ($this->isDue($time, $now, $window)) {
                            $sent += $this->sendOnce($customer, "adhan:{$customer->id}:{$time->toDateString()}:{$prayer}", new AdhanNotification($prayer, $time, $settings));
                        }
                    }
                }
            });

        $this->info("Prayer notifications: {$sent} sent.");

        return self::SUCCESS;
    }

    private function isDue(CarbonInterface $at, CarbonInterface $now, int $window): bool
    {
        return $at->lessThanOrEqualTo($now) && $at->greaterThan($now->subMinutes($window));
    }

    private function sendOnce(Customer $customer, string $claim, CustomerPushNotification $notification): int
    {
        if (! Cache::add($claim, true, now()->addDay())) {
            return 0;
        }

        try {
            $customer->notify($notification);

            return 1;
        } catch (Throwable $e) {
            Cache::forget($claim);
            Log::error('Prayer notification failed.', ['customer_id' => $customer->id, 'key' => $notification->key(), 'error' => $e->getMessage()]);

            return 0;
        }
    }
}
