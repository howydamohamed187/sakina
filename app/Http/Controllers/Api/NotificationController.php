<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
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
}
