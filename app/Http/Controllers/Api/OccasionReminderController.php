<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\OccasionReminderRequest;
use App\Http\Resources\OccasionReminderResource;
use App\Models\Customer;
use App\Models\OccasionReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Every query goes through the authenticated customer's relation, so another
 * customer's reminder behaves exactly like a missing one (404).
 */
class OccasionReminderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $today = now()->startOfDay();

        $reminders = $customer->occasionReminders()
            ->with('dua:id,title')
            ->get()
            ->sortBy(fn (OccasionReminder $reminder): string => ($reminder->is_active ? '0' : '1')
                .$reminder->nextOccurrence($today)->toDateString()
                .str_pad((string) $reminder->id, 10, '0', STR_PAD_LEFT))
            ->values();

        return $this->success(
            $reminders->map(fn (OccasionReminder $reminder): array => (new OccasionReminderResource($reminder))->resolve())->all(),
            __('api.occasion_reminders.list_ready'),
        );
    }

    public function store(OccasionReminderRequest $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $reminder = $customer->occasionReminders()->create([
            ...$request->safe()->only(['title', 'event_date', 'dua_id', 'is_active']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->success(
            (new OccasionReminderResource($reminder->load('dua:id,title')))->resolve(),
            __('api.occasion_reminders.created'),
            201,
        );
    }

    public function show(Request $request, int $reminder): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $model = $customer->occasionReminders()->with('dua:id,title,body')->find($reminder);

        if (! $model) {
            return $this->notFound();
        }

        return $this->success((new OccasionReminderResource($model, detailed: true))->resolve(), __('api.occasion_reminders.ready'));
    }

    public function update(OccasionReminderRequest $request, int $reminder): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $model = $customer->occasionReminders()->find($reminder);

        if (! $model) {
            return $this->notFound();
        }

        $model->update($request->safe()->only(['title', 'event_date', 'dua_id', 'is_active']));

        return $this->success(
            (new OccasionReminderResource($model->load('dua:id,title,body'), detailed: true))->resolve(),
            __('api.occasion_reminders.updated'),
        );
    }

    public function destroy(Request $request, int $reminder): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $model = $customer->occasionReminders()->find($reminder);

        if (! $model) {
            return $this->notFound();
        }

        DB::transaction(function () use ($model): void {
            $model->sentNotifications()->delete();
            $model->delete();
        });

        return $this->success(null, __('api.occasion_reminders.deleted'));
    }

    private function notFound(): JsonResponse
    {
        return $this->error(__('api.occasion_reminders.not_found'), [], 404);
    }

    private function customer(Request $request): Customer|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $user;
    }
}
