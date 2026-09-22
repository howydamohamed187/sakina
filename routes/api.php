<?php

use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactChannelController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\DailyQuestionController;
use App\Http\Controllers\Api\DhikrController;
use App\Http\Controllers\Api\DuaController;
use App\Http\Controllers\Api\HadithController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json([
            'data' => [
                'name' => __('app.name'),
                'status' => 'ok',
            ],
            'message' => __('api.health_ok'),
            'meta' => null,
        ]);
    });

    Route::get('app-config', [AppConfigController::class, 'show']);

    Route::prefix('auth')->group(function () {
        Route::post('login', [CustomerAuthController::class, 'login'])
            ->middleware('throttle.attempts:otp-send');
        Route::post('verify-otp', [CustomerAuthController::class, 'verifyOtp'])
            ->middleware('throttle.attempts:otp-verify');
        Route::post('verify-code', [CustomerAuthController::class, 'verifyAccount'])
            ->middleware('throttle.attempts:otp-verify');
        Route::post('resend-code', [CustomerAuthController::class, 'resendCode'])
            ->middleware('throttle.attempts:otp-send');
        Route::post('register', [CustomerAuthController::class, 'register'])
            ->middleware('throttle.attempts:otp-verify');
        Route::post('forgot-password', [CustomerAuthController::class, 'forgotPassword'])
            ->middleware('throttle.attempts:otp-send');
        Route::post('forget-password', [CustomerAuthController::class, 'forgotPassword'])
            ->middleware('throttle.attempts:otp-send');
        Route::post('reset-password', [CustomerAuthController::class, 'resetPassword'])
            ->middleware('throttle.attempts:otp-verify');
    });

    Route::prefix('admin/auth')->middleware('throttle:10,1')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('me', [CustomerAuthController::class, 'me']);
            Route::post('logout', [CustomerAuthController::class, 'logout']);
            Route::delete('account', [CustomerProfileController::class, 'destroy']);
        });

        Route::prefix('admin/auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'update']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('location', [CustomerProfileController::class, 'updateLocation']);
        Route::put('location', [CustomerProfileController::class, 'updateLocation']);

        Route::get('daily-question', [DailyQuestionController::class, 'show']);
        Route::post('daily-question/answer', [DailyQuestionController::class, 'answer']);

        Route::get('hadiths', [HadithController::class, 'index']);
        Route::get('hadiths/{hadith}', [HadithController::class, 'show']);
        Route::post('hadiths/{hadith}/favorite', [HadithController::class, 'favorite']);
        Route::delete('hadiths/{hadith}/favorite', [HadithController::class, 'unfavorite']);

        Route::get('adhkar', [DhikrController::class, 'index']);
        Route::get('adhkar/{dhikr}', [DhikrController::class, 'show']);
        Route::post('adhkar/{dhikr}/favorite', [DhikrController::class, 'favorite']);
        Route::delete('adhkar/{dhikr}/favorite', [DhikrController::class, 'unfavorite']);

        Route::get('duas', [DuaController::class, 'index']);
        Route::get('duas/{dua}', [DuaController::class, 'show']);
        Route::post('duas/{dua}/favorite', [DuaController::class, 'favorite']);
        Route::delete('duas/{dua}/favorite', [DuaController::class, 'unfavorite']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);

        Route::put('settings', [SettingsController::class, 'update']);
    });

    Route::get('settings', [SettingsController::class, 'show']);

    Route::get('contact-channels', [ContactChannelController::class, 'index']);
});
