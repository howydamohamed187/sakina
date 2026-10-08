<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Settings\RuqyahSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageRuqyah extends SettingsPage
{
    use AuthorizesSettingsPage;
    use HasAdminFormLayout;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = RuqyahSettings::class;

    protected static ?string $slug = 'settings/ruqyah';

    protected static ?int $navigationSort = 71;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.ruqyah');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.ruqyah_admin.settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('app.ruqyah_admin.settings');
    }

    public function getHeading(): string|Htmlable
    {
        return __('app.ruqyah_admin.settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('app.ruqyah_admin.general'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('app.ruqyah_admin.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('app.ruqyah_admin.fields.description'))
                            ->rows(4)
                            ->maxLength(2000),
                    ]),
            ])
            ->columns(1);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['description'] = (string) ($data['description'] ?? '');

        return $data;
    }
}
