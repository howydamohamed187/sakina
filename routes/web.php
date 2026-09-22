<?php

use App\Support\Locales;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (! Locales::isSupported($locale)) {
        return redirect()->back();
    }

    session(['locale' => $locale]);
    app()->setLocale($locale);

    $user = auth('admin')->user() ?? auth()->user();

    if ($user) {
        $user->forceFill(['locale' => $locale])->save();
    }

    return redirect()->back();
})->name('locale.switch');
