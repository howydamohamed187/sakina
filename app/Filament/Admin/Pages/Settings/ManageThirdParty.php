<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Settings\ThirdPartySettings;
use App\Support\EnvFile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
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
                Tabs::make('third_party_settings')
                    ->tabs([
                        Tab::make(__('settings.sections.integrations'))
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
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
                            ]),
                        Tab::make(__('settings.sections.ai'))
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                TextInput::make('ai_provider')
                                    ->label(__('settings.fields.ai_provider'))
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('ai_base_url')
                                    ->label(__('settings.fields.ai_base_url'))
                                    ->required()
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('ai_api_key')
                                    ->label(__('settings.fields.ai_api_key'))
                                    ->maxLength(2000),
                                TextInput::make('ai_model')
                                    ->label(__('settings.fields.ai_model'))
                                    ->required()
                                    ->maxLength(100),
                            ]),
                        Tab::make(__('settings.sections.map'))
                            ->icon('heroicon-o-map')
                            ->schema([
                                TextInput::make('google_map_key')
                                    ->label(__('settings.fields.google_map_key'))
                                    ->helperText(__('settings.helpers.google_map_key'))
                                    ->maxLength(2000),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $current = app(ThirdPartySettings::class);

        foreach (['ai_timeout', 'ai_max_tokens', 'ai_temperature'] as $name) {
            if (! array_key_exists($name, $data)) {
                $data[$name] = $current->{$name};
            }
        }

        return $data;
    }

    public function afterSave(): void
    {
        $settings = app(ThirdPartySettings::class);
        $values = [];

        foreach (ThirdPartySettings::envMap() as $attribute => $key) {
            $values[$key] = $settings->{$attribute};
        }

        app(EnvFile::class)->put($values);
    }
}
