<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_country' => PhoneNumber::countryFromStored($this->phone),
            'phone_national' => PhoneNumber::nationalFromStored($this->phone, PhoneNumber::countryFromStored($this->phone)),
            'phone_formatted' => PhoneNumber::format($this->phone),
            'avatar' => $this->avatarUrl(),
            'status' => $this->status,
            'status_label' => __('app.statuses.'.$this->status),
            'locale' => $this->locale,
            'theme' => $this->theme,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
