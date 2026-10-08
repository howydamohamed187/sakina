<?php

use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactChannelController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\CustomerSettingsController;
use App\Http\Controllers\Api\DailyQuestionController;
use App\Http\Controllers\Api\DailyQuizController;
use App\Http\Controllers\Api\DhikrController;
use App\Http\Controllers\Api\DuaController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HadithController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MosqueController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PrayerTimesController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProfileFavoritesController;
use App\Http\Controllers\Api\QuranController;
use App\Http\Controllers\Api\QuranReciterController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TasbeehController;
use App\Http\Controllers\Api\ZakatController;
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

    Route::get('home/layout', [HomeController::class, 'layout']);

    Route::get('mosques/photo', [MosqueController::class, 'photo'])
        ->middleware('signed')
        ->name('api.mosques.photo');

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

        Route::prefix('profile')->group(function () {
            Route::post('device-token', [CustomerSettingsController::class, 'updateDeviceToken'])->middleware('throttle:30,1');
            Route::get('settings', [CustomerSettingsController::class, 'settings']);
            Route::post('settings', [CustomerSettingsController::class, 'updateSettings']);
            Route::put('settings', [CustomerSettingsController::class, 'updateSettings']);
            Route::post('logout', [CustomerSettingsController::class, 'logout']);
            Route::get('favorites', [ProfileFavoritesController::class, 'index']);
        });

        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'update']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::post('location', [CustomerProfileController::class, 'updateLocation']);
        Route::put('location', [CustomerProfileController::class, 'updateLocation']);

        Route::get('home', [HomeController::class, 'index']);

        Route::get('daily-question', [DailyQuestionController::class, 'show']);
        Route::post('daily-question/answer', [DailyQuestionController::class, 'answer']);

        Route::prefix('daily-quiz')->group(function () {
            Route::get('today', [DailyQuizController::class, 'today']);
            Route::post('today/answer', [DailyQuizController::class, 'answer'])->middleware('throttle:30,1');
            Route::get('statistics', [DailyQuizController::class, 'statistics']);
        });

        Route::post('favorites/toggle', [FavoriteController::class, 'toggle'])->middleware('throttle:60,1');

        Route::get('hadiths', [HadithController::class, 'index']);
        Route::get('hadiths/{hadith}', [HadithController::class, 'show']);

        Route::get('adhkar-categories', [DhikrController::class, 'categories']);
        Route::get('adhkar', [DhikrController::class, 'index']);
        Route::get('adhkar/{dhikr}', [DhikrController::class, 'show']);

        Route::get('duas', [DuaController::class, 'index']);
        Route::get('duas/{dua}', [DuaController::class, 'show']);

        Route::get('tasbeehs', [TasbeehController::class, 'index']);
        Route::get('tasbeehs/{dhikr}', [TasbeehController::class, 'show']);
        Route::post('tasbeehs/{dhikr}/progress', [TasbeehController::class, 'progress'])->middleware('throttle:60,1');

        Route::get('prayer-times', [PrayerTimesController::class, 'index']);

        Route::prefix('quran')->group(function () {
            Route::get('/', [QuranController::class, 'index']);
            Route::get('search', [QuranController::class, 'search'])->middleware('throttle:60,1');
            Route::get('surahs', [QuranController::class, 'surahs']);
            Route::get('surahs/{surah}', [QuranController::class, 'surah']);
            Route::get('surahs/{surah}/ayahs', [QuranController::class, 'ayahs']);
            Route::get('surahs/{surah}/ayahs/{ayah}', [QuranController::class, 'ayah'])->whereNumber('ayah');
            Route::get('ayahs/{ayah}', [QuranController::class, 'ayahByIndex']);
            Route::get('ayahs/{ayah}/tafsir', [QuranController::class, 'tafsir']);

            Route::get('reciters', [QuranReciterController::class, 'index']);
            Route::get('reciters/{reciter}', [QuranReciterController::class, 'show']);
            Route::get('reciters/{reciter}/surahs/{surah}', [QuranReciterController::class, 'surahAudio']);
            Route::get('reciters/{reciter}/surahs/{surah}/audio', [QuranReciterController::class, 'audio']);
            Route::get('reciters/{reciter}/surahs/{surah}/ayahs/{ayah}', [QuranReciterController::class, 'ayahAudio'])->whereNumber('ayah');
        });

        Route::get('mosques/nearby', [MosqueController::class, 'nearby'])->middleware('throttle:60,1');

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);

        Route::put('settings', [SettingsController::class, 'update']);
    });

    Route::get('settings', [SettingsController::class, 'show']);

    Route::get('contact-channels', [ContactChannelController::class, 'index']);

    Route::prefix('zakat')->middleware('throttle:60,1')->group(function () {
        Route::get('nisab', [ZakatController::class, 'nisab']);
        Route::post('calculate', [ZakatController::class, 'calculate']);
        Route::get('payment-suggestions', [ContactChannelController::class, 'index']);
    });
});
