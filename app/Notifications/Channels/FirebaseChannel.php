<?php

namespace App\Notifications\Channels;

use App\Lib\Firebase;
use App\Models\Customer;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Push channel on top of the existing App\Lib\Firebase (FCM v1). Sends to the customer's
 * registered device tokens when push notifications are enabled. Notifications using it
 * implement toFirebase(): array{title: string, body: string, data?: array<string, scalar>,
 * sound?: bool, vibration?: bool, android_channel?: string}.
 */
class FirebaseChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof Customer || ! $notifiable->notifications_enabled || ! method_exists($notification, 'toFirebase')) {
            return;
        }

        $tokens = $notifiable->deviceTokens()->pluck('token')->all();

        if ($tokens === []) {
            return;
        }

        $message = $notification->toFirebase($notifiable);

        try {
            $firebase = Firebase::make()
                ->setTitle($message['title'])
                ->setBody($message['body'])
                ->setMoreData($message['data'] ?? [])
                ->setTokens($tokens);

            if (isset($message['sound']) && method_exists($firebase, 'setSound')) {
                $firebase->setSound((bool) $message['sound']);
            }

            if (isset($message['vibration']) && method_exists($firebase, 'setVibration')) {
                $firebase->setVibration((bool) $message['vibration']);
            }

            if (isset($message['android_channel']) && method_exists($firebase, 'setAndroidChannel')) {
                $firebase->setAndroidChannel((string) $message['android_channel']);
            }

            $firebase->do();
        } catch (Throwable $e) {
            Log::warning('Firebase push failed.', ['customer_id' => $notifiable->getKey(), 'error' => $e->getMessage()]);
        }
    }
}
