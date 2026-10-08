<?php

namespace App\Notifications;

use App\Lib\Firebase;
use App\Models\Customer;
use App\Support\Locales;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Same flow as SendAdminMessagesNotification: stored through the database channel, and
 * toArray() pushes it with App\Lib\Firebase to the customer's device tokens in their
 * language. A push failure never blocks the stored notification.
 */
abstract class CustomerPushNotification extends Notification
{
    use Queueable;

    abstract public function key(): string;

    /**
     * @return array<string, string> locale => title
     */
    abstract public function titles(): array;

    /**
     * @return array<string, string> locale => body
     */
    abstract public function bodies(): array;

    /**
     * Shown to Flutter as `data` / entity_type / entity_id in the notifications list.
     *
     * @return array<string, scalar|null>
     */
    public function viewData(): array
    {
        return [];
    }

    /**
     * Extra FCM data (values are sent as strings).
     *
     * @return array<string, scalar|null>
     */
    public function pushData(): array
    {
        return [];
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toFirebase(object $notifiable): void
    {
        if (! $notifiable instanceof Customer || ! $notifiable->notifications_enabled) {
            return;
        }

        $tokens = $notifiable->deviceTokens()->pluck('token')->unique()->values()->all();

        if ($tokens === []) {
            return;
        }

        $locale = $notifiable->preferredLocale();
        $data = array_map('strval', array_filter([
            'key' => $this->key(),
            ...$this->viewData(),
            ...$this->pushData(),
        ], fn ($value): bool => $value !== null));

        $firebase = null;

        try {
            $firebase = Firebase::make()
                ->setTokens($tokens)
                ->setTitle($this->pick($this->titles(), $locale))
                ->setBody($this->pick($this->bodies(), $locale))
                ->setMoreData($data);
            $firebase->do();
        } catch (Throwable $e) {
            Log::warning('Firebase push failed.', ['customer_id' => $notifiable->getKey(), 'key' => $this->key(), 'error' => $e->getMessage()]);
        }

        try {
            $firebase = null;
        } catch (Throwable) {
            // Firebase retries from __destruct when do() failed; already logged above.
        }
    }

    public function toArray(object $notifiable): array
    {
        $this->toFirebase($notifiable);

        return [
            'key' => $this->key(),
            'title' => $this->titles(),
            'body' => $this->bodies(),
            'format' => 'filament',
            'viewData' => $this->viewData(),
            'duration' => 'persistent',
            'status' => 'success',
        ];
    }

    /**
     * Translates notifications.{$key}.title/body in every supported locale.
     *
     * @param  Closure(string): array<string, scalar>|array<string, scalar>  $replace
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    protected static function translate(string $key, Closure|array $replace = []): array
    {
        $titles = [];
        $bodies = [];

        foreach (Locales::all() as $locale) {
            $values = $replace instanceof Closure ? $replace($locale) : $replace;
            $titles[$locale] = __("notifications.{$key}.title", $values, $locale);
            $bodies[$locale] = __("notifications.{$key}.body", $values, $locale);
        }

        return [$titles, $bodies];
    }

    /**
     * @param  array<string, string>  $texts
     */
    private function pick(array $texts, ?string $locale): string
    {
        return (string) ($texts[$locale ?? ''] ?? $texts[Locales::default()] ?? reset($texts) ?: '');
    }
}
