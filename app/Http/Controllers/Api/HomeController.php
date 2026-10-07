<?php

namespace App\Http\Controllers\Api;

use App\Services\HomeService;
use App\Services\Providers\ProviderException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends ApiController
{
    public function __construct(private readonly HomeService $home) {}

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make(
            [
                'X-Latitude' => $request->header('X-Latitude'),
                'X-Longitude' => $request->header('X-Longitude'),
                'X-Timezone' => $request->header('X-Timezone'),
            ],
            [
                'X-Latitude' => ['required', 'numeric', 'between:-90,90'],
                'X-Longitude' => ['required', 'numeric', 'between:-180,180'],
                'X-Timezone' => ['nullable', 'string', 'timezone:all'],
            ],
            [
                'required' => __('api.location_header_required'),
                'numeric' => __('api.location_header_numeric'),
                'between' => __('api.location_header_between'),
                'timezone' => __('api.location_header_timezone'),
            ],
            [
                'X-Latitude' => 'X-Latitude',
                'X-Longitude' => 'X-Longitude',
                'X-Timezone' => 'X-Timezone',
            ]
        );

        if ($validator->fails()) {
            return $this->error(__('api.location_headers_required'), $validator->errors()->toArray());
        }

        try {
            $data = $this->home->data(
                user: $request->user(),
                latitude: (float) $request->header('X-Latitude'),
                longitude: (float) $request->header('X-Longitude'),
                timezone: $request->header('X-Timezone') ?: null,
            );
        } catch (ProviderException $e) {
            return $this->error($e->userMessage(), [], $e->status());
        }

        return $this->success($data, __('api.home_ready'));
    }

    public function layout(): JsonResponse
    {
        return $this->success($this->home->layout(), __('api.home_layout_ready'));
    }
}
