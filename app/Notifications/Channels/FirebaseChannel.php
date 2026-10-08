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
 * implement toFirebase(): array{title: string, body: string, data?: array<string, scalar>}.
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
            Firebase::make()
                ->setTitle($message['title'])
                ->setBody($message['body'])
                ->setMoreData($message['data'] ?? [])
                ->setTokens($tokens)
                ->send();
        } catch (Throwable $e) {
            Log::warning('Firebase push failed.', ['customer_id' => $notifiable->getKey(), 'error' => $e->getMessage()]);
        }
    }
}
