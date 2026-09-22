<?php

namespace App\Http\Controllers\Api;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class ApiController extends Controller
{
    protected function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return ApiResponse::json($status, $message ?? __('api.success'), $data);
    }

    protected function error(string $message, array $errors = [], int $status = 422, mixed $data = null): JsonResponse
    {
        return ApiResponse::json($status, $message, $data, $errors);
    }

    protected function paginated(AnonymousResourceCollection $resource, LengthAwarePaginator $paginator): JsonResponse
    {
        return ApiResponse::json(200, __('api.success'), $resource->resolve());
    }
}
