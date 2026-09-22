<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Settings\ThirdPartySettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageThirdParty extends SettingsPage
{
    use AuthorizesSettingsPage;
    use HasAdminFormLayout;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static string $settings = ThirdPartySettings::class;

    protected static ?string $slug = 'settings/third-party';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('settings.nav');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.third_party');
    }

    public function getTitle(): string|Htmlable
    {
        return __('settings.third_party_heading');
    }

    public function getHeading(): string|Htmlable
    {
        return __('settings.third_party_heading');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('settings.sections.integrations'))
                    ->schema([
                        TextInput::make('google_map_key')
                            ->label(__('settings.fields.google_map_key'))
                            ->helperText(__('settings.helpers.google_map_key'))
                            ->maxLength(255),
                        TextInput::make('project_name')
                            ->label(__('settings.fields.firebase_project'))
                            ->maxLength(255),
                        FileUpload::make('firebase_file')
                            ->label(__('settings.fields.firebase_file'))
                            ->acceptedFileTypes(['application/json', 'text/plain'])
                            ->directory('settings/firebase')
                            ->disk('local')
                            ->visibility('private')
                            ->downloadable(),
                    ])
                    ->columns(1),
            ])
            ->columns(1);
    }
}
