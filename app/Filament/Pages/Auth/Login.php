<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    use UsesGuestArabicLocale;

    public function mount(): void
    {
        $this->applyGuestLocale();

        parent::mount();
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->throwFailureValidationException(__('api.login_failed'));
        }

        if ($user->status !== 'active') {
            $this->throwFailureValidationException(__('api.account_inactive'));
        }

        if (! $user->canAccessPanel(Filament::getCurrentPanel())) {
            $this->throwFailureValidationException(__('api.login_no_role'));
        }

        Filament::auth()->login($user, (bool) ($data['remember'] ?? false));

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(?string $message = null): never
    {
        throw ValidationException::withMessages([
            'data.email' => $message ?? __('api.login_failed'),
        ]);
    }
}
