<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Validation\Rules\Password;

class AdminForm
{
    public static function section(string $title, array $schema, int $columns = 2): Section
    {
        return Section::make($title)
            ->schema($schema)
            ->columns($columns);
    }

    public static function avatar(string $directory): FileUpload
    {
        return FileUpload::make('avatar')
            ->label(__('app.fields.avatar'))
            ->image()
            ->imageEditor()
            ->imageCropAspectRatio('1:1')
            ->imageResizeMode('cover')
            ->imagePreviewHeight('240')
            ->directory($directory)
            ->disk('public')
            ->visibility('public')
            ->maxSize(2048)
            ->extraAttributes(['class' => 'fi-fo-admin-avatar'])
            ->columnSpanFull();
    }

    public static function password(): TextInput
    {
        return TextInput::make('password')
            ->label(__('app.fields.password'))
            ->password()
            ->revealable()
            ->rule(Password::defaults())
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->helperText(__('app.profile_password_hint'))
            ->extraInputAttributes(['dir' => 'ltr']);
    }

    public static function passwordConfirmation(): TextInput
    {
        return TextInput::make('password_confirmation')
            ->label(__('app.fields.password_confirmation'))
            ->password()
            ->revealable()
            ->dehydrated(false)
            ->required(fn (Get $get): bool => filled($get('password')))
            ->same('password')
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->extraInputAttributes(['dir' => 'ltr']);
    }

    public static function statusToggle(): Toggle
    {
        return Toggle::make('status')
            ->label(__('app.fields.status'))
            ->helperText(fn (Get $get): string => static::statusIsActive($get('status'))
                ? __('app.statuses.active')
                : __('app.statuses.inactive'))
            ->onColor('success')
            ->offColor('gray')
            ->inline(false)
            ->default(true)
            ->live()
            ->afterStateHydrated(function (Toggle $component, $state): void {
                $component->state(static::statusIsActive($state));
            })
            ->dehydrateStateUsing(fn ($state): string => static::statusIsActive($state) ? 'active' : 'suspended');
    }

    public static function statusIsActive(mixed $state): bool
    {
        return $state === true || $state === 'active' || $state === 1 || $state === '1';
    }
}
