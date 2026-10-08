<?php

namespace App\Http\Controllers\Api;

use App\Http\ApiResponse;
use App\Http\Resources\CustomerNotificationResource;
use App\Http\Resources\NotificationResource;
use App\Models\Customer;
use App\Notifications\SendAdminMessagesNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $paginator = $request->user()
            ->notifications()
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->paginated(
            NotificationResource::collection($paginator->getCollection()),
            $paginator
        );
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if (! $notification) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $notification->markAsRead();

        return $this->success(
            (new NotificationResource($notification->fresh()))->resolve(),
            __('api.notification_read')
        );
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success(null, __('api.notifications_read'));
    }

    /**
     * Paginated list with optional search (q / search); the returned page is marked as read.
     */
    public function all(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $query = $request->query('q', $request->query('search', ''));
        $search = is_string($query) ? mb_substr(trim($query), 0, 200) : '';

        $paginator = $customer->notifications()
            ->latest()
            ->when($search !== '', fn ($builder) => $builder->where('data', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->paginate(10)
            ->withQueryString();

        $unreadCount = $customer->unreadNotifications()->count();
        $pageIds = $paginator->getCollection()->pluck('id');

        if ($pageIds->isNotEmpty()) {
            $customer->unreadNotifications()->whereKey($pageIds)->update(['read_at' => now()]);
        }

        return ApiResponse::json(
            200,
            __('api.notifications_list'),
            CustomerNotificationResource::collection($paginator->getCollection())->resolve(),
            [],
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'unread_notifications_count' => $unreadCount,
                'search_query' => $search !== '' ? $search : null,
            ],
        );
    }

    /**
     * Marks the given ids (id or ids[]) as read, or all unread notifications when none are sent.
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $validated = $request->validate([
            'id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'ids' => ['sometimes', 'nullable', 'array', 'max:100'],
            'ids.*' => ['string', 'max:64'],
        ]);

        $ids = array_filter([...($validated['ids'] ?? []), $validated['id'] ?? null]);

        $customer->unreadNotifications()
            ->when($ids !== [], fn ($builder) => $builder->whereKey($ids))
            ->update(['read_at' => now()]);

        return $this->success(
            ['unread_notifications_count' => $customer->unreadNotifications()->count()],
            __('api.notifications_read'),
        );
    }

    /**
     * Deletes one notification, or all of the customer's notifications when no id is given.
     */
    public function destroy(Request $request, ?string $id = null): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        if ($id === null || $id === '') {
            $customer->notifications()->delete();
        } elseif ($customer->notifications()->whereKey($id)->delete() === 0) {
            return $this->error(__('api.not_found'), [], 404);
        }

        return $this->success(null, __('api.notifications_deleted'));
    }

    public function fcm(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'array'],
            'title.*' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'array'],
            'body.*' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->notify(new SendAdminMessagesNotification($validated['title'], $validated['body']));

        return $this->success(null, __('api.notifications_fcm_sent'));
    }
}
