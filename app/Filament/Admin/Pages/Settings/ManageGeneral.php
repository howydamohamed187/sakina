<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Forms\Components\GoogleMapLocation;
use App\Filament\Forms\Components\PhoneField;
use App\Forms\Components\SelectFontAwesomeIcon;
use App\Settings\AppearanceSettings;
use App\Settings\GeneralSettings;
use App\Support\Fonts;
use App\Support\PhoneNumber;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageGeneral extends SettingsPage
{
    use AuthorizesSettingsPage;
    use HasAdminFormLayout;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $slug = 'settings/general';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('settings.nav');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.general');
    }

    public function getTitle(): string|Htmlable
    {
        return __('settings.general_heading');
    }

    public function getHeading(): string|Htmlable
    {
        return __('settings.general_heading');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('panel_settings')
                    ->tabs([
                        Tab::make(__('settings.sections.identity'))
                            ->schema($this->identitySchema()),
                        Tab::make(__('settings.sections.social_links'))
                            ->icon('heroicon-o-share')
                            ->schema($this->socialLinksSchema()),
                        Tab::make(__('app.settings'))
                            ->schema($this->appearanceSchema()),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    /**
     * @return array<int, Component>
     */
    protected function identitySchema(): array
    {
        return [
            Section::make(__('settings.sections.identity'))
                ->schema([
                    FileUpload::make('app_logo')
                        ->label(__('settings.fields.app_logo'))
                        ->image()
                        ->directory('settings')
                        ->disk('public')
                        ->visibility('public')
                        ->imageEditor()
                        ->maxSize(2048),
                    FileUpload::make('fav_icon')
                        ->label(__('settings.fields.favicon'))
                        ->image()
                        ->directory('settings')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(1024),
                ])
                ->columns(2),
            Section::make()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('app_name')
                        ->label(__('settings.fields.app_name'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->translatable(),
            Section::make()
                ->schema([
                    TextInput::make('app_email')
                        ->label(__('settings.fields.app_email'))
                        ->email()
                        ->maxLength(255),
                    TextInput::make('app_address')
                        ->label(__('settings.fields.address'))
                        ->maxLength(255),
                    PhoneField::make('app_whatsapp')
                        ->label(__('settings.fields.app_whatsapp'))
                        ->withoutUnique()
                        ->columnSpanFull(),
                    GoogleMapLocation::make('app_location')
                        ->label(__('settings.fields.location'))
                        ->helperText(__('settings.helpers.location')),
                ])
                ->columns(2),
            Section::make(__('settings.sections.applications_links'))
                ->schema([
                    TextInput::make('applications_links.google_play_link')
                        ->label(__('settings.fields.google_play_link'))
                        ->url()
                        ->maxLength(255),
                    TextInput::make('applications_links.apple_store_link')
                        ->label(__('settings.fields.apple_store_link'))
                        ->url()
                        ->maxLength(255),
                ])
                ->columns(1),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function socialLinksSchema(): array
    {
        return [
            Section::make()
                ->description(__('settings.helpers.social_links'))
                ->schema([
                    Repeater::make('social_links')
                        ->label(__('settings.sections.social_links'))
                        ->addActionLabel(__('settings.add_social_link'))
                        ->columns(2)
                        ->schema([
                            SelectFontAwesomeIcon::make('icon')
                                ->label(__('settings.fields.social_icon'))
                                ->searchable()
                                ->allowHtml(),
                            TextInput::make('link')
                                ->label(__('settings.fields.social_link'))
                                ->url(),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['link'] ?? null),
                ]),
        ];
    }

    /**
     * @return array<int, Component>
     */
    protected function appearanceSchema(): array
    {
        return [
            Section::make(__('settings.sections.panel_appearance'))
                ->description(__('settings.helpers.panel_appearance'))
                ->schema([
                    Select::make('font_family')
                        ->label(__('settings.fields.font_family'))
                        ->options(Fonts::families())
                        ->required()
                        ->native(false)
                        ->searchable(),
                    Select::make('font_size')
                        ->label(__('settings.fields.font_size'))
                        ->options(Fonts::sizeOptions())
                        ->required()
                        ->native(false),
                ])
                ->columns(2),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $appearance = app(AppearanceSettings::class);

        $data['font_family'] = $appearance->font_family ?: Fonts::DEFAULT_FAMILY;
        $data['font_size'] = $appearance->font_size ?: Fonts::DEFAULT_SIZE;
        $data['app_name'] = $this->normalizeAppName($data['app_name'] ?? null);
        $data['app_location'] = [
            'lat' => (float) ($data['app_latitude'] ?? GoogleMapLocation::DEFAULT_LAT),
            'lng' => (float) ($data['app_longitude'] ?? GoogleMapLocation::DEFAULT_LNG),
        ];
        $data['social_links'] = $this->normalizeSocialLinks($data['social_links'] ?? []);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $raw = is_array($this->data) ? $this->data : [];
        $data = array_replace($raw, $data);

        $names = $this->normalizeAppName($raw['app_name'] ?? $data['app_name'] ?? null);
        $data['app_name'] = $names;

        $location = $raw['app_location'] ?? $data['app_location'] ?? [];
        $data['app_latitude'] = (string) ($location['lat'] ?? GoogleMapLocation::DEFAULT_LAT);
        $data['app_longitude'] = (string) ($location['lng'] ?? GoogleMapLocation::DEFAULT_LNG);

        $appearance = app(AppearanceSettings::class);
        $appearance->font_family = $raw['font_family'] ?? $data['font_family'] ?? Fonts::DEFAULT_FAMILY;
        $appearance->font_size = $raw['font_size'] ?? $data['font_size'] ?? Fonts::DEFAULT_SIZE;
        $appearance->save();

        $whatsapp = $raw['app_whatsapp'] ?? $data['app_whatsapp'] ?? null;
        $data['app_whatsapp'] = is_array($whatsapp)
            ? PhoneNumber::toE164($whatsapp['country'] ?? null, $whatsapp['national'] ?? null)
            : $whatsapp;

        $data['social_links'] = $this->normalizeSocialLinks($raw['social_links'] ?? $data['social_links'] ?? []);

        unset($data['font_family'], $data['font_size'], $data['app_location']);

        return array_intersect_key($data, array_flip(array_keys(GeneralSettings::defaults())));
    }

    public function getRedirectUrl(): ?string
    {
        return static::getUrl();
    }

    /**
     * @return array{ar: string, en: string}
     */
    private function normalizeAppName(mixed $name): array
    {
        if (is_array($name)) {
            return [
                'ar' => (string) ($name['ar'] ?? ''),
                'en' => (string) ($name['en'] ?? ''),
            ];
        }

        $fallback = is_string($name) && $name !== '' ? $name : 'سكينة';

        return [
            'ar' => $fallback,
            'en' => $fallback === 'سكينة' ? 'Sakina' : $fallback,
        ];
    }

    /**
     * @param  mixed  $links
     * @return array<int, array{icon: string|null, link: string}>
     */
    private function normalizeSocialLinks(mixed $links): array
    {
        if (! is_array($links)) {
            return [];
        }

        return collect($links)
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['link'] ?? null))
            ->map(fn (array $item): array => [
                'icon' => filled($item['icon'] ?? null) ? (string) $item['icon'] : null,
                'link' => (string) $item['link'],
            ])
            ->values()
            ->all();
    }
}
