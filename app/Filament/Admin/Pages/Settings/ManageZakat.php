<?php

namespace App\Filament\Admin\Pages\Settings;

use App\Filament\Concerns\AuthorizesSettingsPage;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Services\Zakat\ZakatService;
use App\Settings\ZakatSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageZakat extends SettingsPage
{
    use AuthorizesSettingsPage;
    use HasAdminFormLayout;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static string $settings = ZakatSettings::class;

    protected static ?string $slug = 'settings/zakat';

    protected static ?int $navigationSort = 18;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.zakat');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.zakat_admin.settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('app.zakat_admin.settings');
    }

    public function getHeading(): string|Htmlable
    {
        return __('app.zakat_admin.settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('app.zakat_admin.calculation'))
                    ->description(__('app.zakat_admin.calculation_hint'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('app.zakat_admin.fields.calculator_active'))
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false)
                            ->columnSpanFull(),
                        TextInput::make('zakat_percentage')
                            ->label(__('app.zakat_admin.fields.zakat_percentage'))
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%'),
                        TextInput::make('nisab_gold_grams')
                            ->label(__('app.zakat_admin.fields.nisab_gold_grams'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(10000)
                            ->step(0.01)
                            ->suffix(__('app.zakat_admin.grams')),
                        Select::make('gold_karat')
                            ->label(__('app.zakat_admin.fields.gold_karat'))
                            ->options(collect(config('zakat.karats'))->mapWithKeys(fn (int $karat): array => [$karat => $karat.'K'])->all())
                            ->required()
                            ->native(false),
                        Select::make('default_currency')
                            ->label(__('app.zakat_admin.fields.default_currency'))
                            ->options(array_combine(ZakatService::currencies(), ZakatService::currencies()))
                            ->required()
                            ->searchable()
                            ->native(false),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['zakat_percentage'] = (float) $data['zakat_percentage'];
        $data['nisab_gold_grams'] = (float) $data['nisab_gold_grams'];
        $data['gold_karat'] = (int) $data['gold_karat'];

        return $data;
    }
}
