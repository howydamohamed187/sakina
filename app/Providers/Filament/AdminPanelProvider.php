<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard as AdminDashboard;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use App\Filament\Pages\Auth\PasswordReset\ResetPassword;
use App\Http\Middleware\SetLocale;
use App\Support\StoredSettings;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->profile(EditProfile::class, isSimple: false)
            ->databaseNotifications()
            ->darkMode(true)
            ->brandName(fn (): string => StoredSettings::appName())
            ->brandLogo(fn (): ?string => StoredSettings::logoUrl())
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => StoredSettings::faviconUrl())
            ->font('Alexandria', provider: GoogleFontProvider::class)
            ->colors([
                'primary' => Color::Teal,
            ])
            ->sidebarWidth('16.75rem')
            ->maxContentWidth(MaxWidth::Full)
            ->navigationGroups([
                NavigationGroup::make()->label(fn (): string => __('app.nav.user_management')),
                NavigationGroup::make()->label(fn (): string => __('app.nav.zakat')),
                NavigationGroup::make()->label(fn (): string => __('app.nav.daily_questions')),
                NavigationGroup::make()->label(fn (): string => __('app.nav.hadiths')),
                NavigationGroup::make()->label(fn (): string => __('app.nav.adhkar')),
                NavigationGroup::make()->label(fn (): string => __('app.nav.duas')),
                NavigationGroup::make()->label(fn (): string => __('app.nav.permission_management')),
                NavigationGroup::make()->label(fn (): string => __('settings.nav')),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                AdminDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.admin.hooks.locale-switcher')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => Blade::render(
                    '<script>localStorage.setItem("theme", @json(auth()->user()?->theme ?? session("theme", "system")));</script>'
                ),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.admin.hooks.theme')->render(),
            );
    }
}
