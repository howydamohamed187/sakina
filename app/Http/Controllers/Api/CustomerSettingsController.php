<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UpdateCustomerSettingsRequest;
use App\Http\Requests\Api\UpdateDeviceTokenRequest;
use App\Http\Requests\Api\UpdatePrayerNotificationSettingsRequest;
use App\Models\Customer;
use App\Models\CustomerDeviceToken;
use App\Services\PrayerNotificationPreferences;
use App\Services\PrayerTimesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CustomerSettingsController extends ApiController
{
    /**
     * Registers the FCM token of the current device. A token belongs to whoever registered
     * it last (shared phone, account switch).
     */
    public function updateDeviceToken(UpdateDeviceTokenRequest $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        CustomerDeviceToken::query()->updateOrCreate(
            ['token' => (string) $request->validated('token')],
            [
                'customer_id' => $customer->id,
                'personal_access_token_id' => $this->accessTokenId($request),
                'platform' => $request->validated('platform'),
            ],
        );

        return $this->success(null, __('api.device_token_updated'));
    }

    public function settings(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $this->success($this->payload($customer), __('api.customer_settings_ready'));
    }

    public function updateSettings(UpdateCustomerSettingsRequest $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $data = $request->validated();
        $changes = [];

        if (array_key_exists('notification_status', $data)) {
            $changes['notifications_enabled'] = (bool) $data['notification_status'];
        }

        if (array_key_exists('preferred_language', $data)) {
            $changes['locale'] = $data['preferred_language'];
            app()->setLocale($data['preferred_language']);
        }

        if ($changes !== []) {
            $customer->forceFill($changes)->save();
        }

        return $this->success($this->payload($customer->fresh()), __('api.customer_settings_updated'));
    }

    public function prayerNotifications(Request $request, PrayerNotificationPreferences $preferences): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $this->success($preferences->for($customer), __('api.prayer_notifications.ready'));
    }

    public function updatePrayerNotifications(UpdatePrayerNotificationSettingsRequest $request, PrayerNotificationPreferences $preferences): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $this->success(
            $preferences->update($customer, $request->safe()->only(PrayerTimesService::TIMES)),
            __('api.prayer_notifications.updated'),
        );
    }

    /**
     * Ends the current session and stops push notifications to this device.
     */
    public function logout(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $accessTokenId = $this->accessTokenId($request);
        $deviceToken = $request->input('device_token', $request->input('token'));

        if ($accessTokenId) {
            $customer->deviceTokens()->where('personal_access_token_id', $accessTokenId)->delete();
        }

        if (is_string($deviceToken) && $deviceToken !== '') {
            $customer->deviceTokens()->where('token', $deviceToken)->delete();
        }

        $customer->currentAccessToken()?->delete();

        return $this->success(null, __('api.logout_success'));
    }

    /**
     * @return array{notification_status: int, preferred_language: string}
     */
    private function payload(Customer $customer): array
    {
        return [
            'notification_status' => $customer->notifications_enabled ? 1 : 0,
            'preferred_language' => $customer->locale,
        ];
    }

    private function accessTokenId(Request $request): ?int
    {
        $token = $request->user()?->currentAccessToken();

        return $token instanceof PersonalAccessToken ? (int) $token->getKey() : null;
    }
}
