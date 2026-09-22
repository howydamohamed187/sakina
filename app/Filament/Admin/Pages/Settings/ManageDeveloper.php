<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Settings\DeveloperSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageDeveloper extends SettingsPage
{
    use AuthorizesSettingsPage;
    use HasAdminFormLayout;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static string $settings = DeveloperSettings::class;

    protected static ?string $slug = 'settings/developer';

    protected static ?int $navigationSort = 4;

    protected static function isSuperAdminOnly(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('settings.nav');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.developer');
    }

    public function getTitle(): string|Htmlable
    {
        return __('settings.developer_heading');
    }

    public function getHeading(): string|Htmlable
    {
        return __('settings.developer_heading');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('settings.developer'))
                    ->schema([
                        Toggle::make('debug_mode')
                            ->label(__('settings.fields.debug_mode'))
                            ->helperText(__('settings.helpers.debug_mode'))
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                        Toggle::make('otp_code_is_random')
                            ->label(__('settings.fields.otp_code_is_random'))
                            ->helperText(__('settings.helpers.otp_code_is_random'))
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
