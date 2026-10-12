<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Forms\Components\LockedSecretInput;
use App\Rules\KeepsEncryptedSetting;
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
                                $this->envField('ai_provider'),
                                $this->envField('ai_base_url'),
                                $this->envField('ai_api_key'),
                                $this->envField('ai_model'),
                            ]),
                        Tab::make(__('settings.sections.map'))
                            ->icon('heroicon-o-map')
                            ->schema([
                                $this->envField('google_map_key'),
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
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (array_keys(ThirdPartySettings::envMap()) as $name) {
            $data[$name] = ThirdPartySettings::ciphertext($name);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $current = app(ThirdPartySettings::class);

        foreach (array_keys(ThirdPartySettings::envMap()) as $name) {
            $incoming = $data[$name] ?? null;

            if (LockedSecretInput::isUnchanged($incoming) || ThirdPartySettings::isSameCiphertext($name, $incoming)) {
                $data[$name] = $current->{$name};

                continue;
            }

            $data[$name] = $this->castEnvAttribute($name, trim((string) $incoming));
        }

        return $data;
    }

    public function afterSave(): void
    {
        $settings = app(ThirdPartySettings::class);
        $values = [];

        foreach (ThirdPartySettings::envMap() as $attribute => $key) {
            $values[$key] = $settings->{$attribute};
            $this->data[$attribute] = ThirdPartySettings::ciphertext($attribute);
        }

        app(EnvFile::class)->put($values);
    }

    private function envField(string $name): LockedSecretInput
    {
        return LockedSecretInput::make($name)
            ->label(__('settings.fields.'.$name))
            ->helperText(__('settings.helpers.env_synced'))
            ->rules([
                new KeepsEncryptedSetting($name, $this->plainRules($name)),
            ]);
    }

    /**
     * @return list<string>
     */
    private function plainRules(string $name): array
    {
        return match ($name) {
            'ai_base_url' => ['required', 'url', 'max:255'],
            'ai_timeout' => ['required', 'integer', 'min:1', 'max:300'],
            'ai_max_tokens' => ['required', 'integer', 'min:1', 'max:16000'],
            'ai_temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'ai_api_key', 'google_map_key' => ['nullable', 'string', 'max:2000'],
            default => ['required', 'string', 'max:100'],
        };
    }

    private function castEnvAttribute(string $name, string $value): string|int|float|null
    {
        return match ($name) {
            'ai_timeout', 'ai_max_tokens' => (int) $value,
            'ai_temperature' => (float) $value,
            'ai_api_key', 'google_map_key' => $value === '' ? null : $value,
            default => $value,
        };
    }
}
