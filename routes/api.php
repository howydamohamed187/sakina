<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json([
            'data' => [
                'name' => 'سكينة',
                'status' => 'ok',
            ],
            'message' => 'success',
            'meta' => null,
        ]);
    });

    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
