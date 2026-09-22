<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ContactChannelResource;
use App\Models\ContactChannel;
use Illuminate\Http\JsonResponse;

class ContactChannelController extends ApiController
{
    public function index(): JsonResponse
    {
        $channels = ContactChannel::query()
            ->with('contactType')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->success(
            ContactChannelResource::collection($channels)->resolve(),
            __('api.success')
        );
    }
}
