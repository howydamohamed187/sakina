<?php

namespace App\Http\Resources;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Customer */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $verified = $this->email_verified_at !== null;

        return [
            'id' => $this->id,
            'avatar' => $this->avatarUrl() ?: '',
            'name' => $this->name,
            'email' => $this->email,
            'location' => $this->location,
            'address' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'locale' => $this->locale,
            'preferred_language' => $this->locale,
            'status' => $this->status,
            'status_label' => __('app.statuses.'.$this->status),
            'email_verified' => $verified ? 1 : 0,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'need_activation' => $verified ? 0 : 1,
            'unread_notifications_count' => $this->unreadNotifications()->count(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
