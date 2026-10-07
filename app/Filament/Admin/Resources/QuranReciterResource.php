<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QuranReciterResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Forms\AdminForm;
use App\Models\QuranReciter;
use App\Support\Permissions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuranReciterResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = QuranReciter::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_QURAN_RECITERS;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    protected static ?int $navigationSort = 71;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.quran');
    }

    public static function getModelLabel(): string
    {
        return __('app.quran_admin.reciter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.quran_admin.reciters');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.quran_admin.reciters');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.quran_admin.reciter_details'), [
                    TextInput::make('name.ar')
                        ->label(__('app.quran_admin.fields.name_ar'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('name.en')
                        ->label(__('app.quran_admin.fields.name_en'))
                        ->maxLength(255),
                    TextInput::make('name.ckb')
                        ->label(__('app.quran_admin.fields.name_ckb'))
                        ->maxLength(255),
                    FileUpload::make('image')
                        ->label(__('app.quran_admin.fields.image'))
                        ->image()
                        ->imageEditor()
                        ->directory('quran-reciters')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(2048)
                        ->columnSpanFull(),
                    Toggle::make('is_default')
                        ->label(__('app.quran_admin.fields.is_default'))
                        ->helperText(__('app.quran_admin.default_hint'))
                        ->inline(false),
                    AdminForm::statusToggle(),
                ]),
                AdminForm::section(__('app.quran_admin.audio'), [
                    TextInput::make('ayah_audio_url_template')
                        ->label(__('app.quran_admin.fields.ayah_audio_url_template'))
                        ->helperText(__('app.quran_admin.audio_template_hint'))
                        ->maxLength(255)
                        ->rule('regex:/^https?:\/\/\S+$/')
                        ->rule('regex:/\{surah\}.*\{ayah\}|\{ayah\}.*\{surah\}/')
                        ->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('surah_audio_url_template')
                        ->label(__('app.quran_admin.fields.surah_audio_url_template'))
                        ->helperText(__('app.quran_admin.surah_audio_template_hint'))
                        ->maxLength(255)
                        ->rule('regex:/^https?:\/\/\S+$/')
                        ->rule('regex:/\{surah(_number)?\}/')
                        ->extraInputAttributes(['dir' => 'ltr']),
                ], 1),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('app.quran_admin.reciter_details'))
                    ->schema([
                        ImageEntry::make('image')
                            ->label(__('app.quran_admin.fields.image'))
                            ->disk('public')
                            ->circular(),
                        TextEntry::make('name.ar')->label(__('app.quran_admin.fields.name_ar')),
                        TextEntry::make('name.en')->label(__('app.quran_admin.fields.name_en'))->placeholder('—'),
                        TextEntry::make('name.ckb')->label(__('app.quran_admin.fields.name_ckb'))->placeholder('—'),
                        TextEntry::make('is_default')
                            ->label(__('app.quran_admin.fields.is_default'))
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? __('app.statuses.active') : '—'),
                        static::statusEntry(),
                    ])
                    ->columns(2),
                InfoSection::make(__('app.quran_admin.audio'))
                    ->schema([
                        TextEntry::make('ayah_audio_url_template')
                            ->label(__('app.quran_admin.fields.ayah_audio_url_template'))
                            ->placeholder('—'),
                        TextEntry::make('surah_audio_url_template')
                            ->label(__('app.quran_admin.fields.surah_audio_url_template'))
                            ->placeholder('—'),
                        TextEntry::make('sample_audio')
                            ->label(__('app.quran_admin.sample_audio'))
                            ->state(fn (QuranReciter $record): ?string => $record->surahAudioUrl(1))
                            ->url(fn (QuranReciter $record): ?string => $record->surahAudioUrl(1), shouldOpenInNewTab: true)
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label(__('app.quran_admin.fields.image'))
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->state(fn (QuranReciter $record): string => $record->displayName())
                    ->searchable(query: fn ($query, string $search) => $query->where('name', 'like', "%{$search}%")),
                IconColumn::make('is_default')
                    ->label(__('app.quran_admin.fields.is_default'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'active'
                        ? __('app.statuses.active')
                        : __('app.statuses.inactive')),
            ])
            ->actions([
                Action::make('setDefault')
                    ->label(__('app.quran_admin.set_default'))
                    ->icon('heroicon-o-star')
                    ->visible(fn (QuranReciter $record): bool => ! $record->is_default && static::canEdit($record))
                    ->action(fn (QuranReciter $record) => $record->update(['is_default' => true])),
                ViewAction::make()->label(__('app.actions.view')),
                EditAction::make()->label(__('app.actions.edit')),
                DeleteAction::make()->label(__('app.actions.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuranReciters::route('/'),
            'create' => Pages\CreateQuranReciter::route('/create'),
            'view' => Pages\ViewQuranReciter::route('/{record}'),
            'edit' => Pages\EditQuranReciter::route('/{record}/edit'),
        ];
    }

    private static function statusEntry(): TextEntry
    {
        return TextEntry::make('status')
            ->label(__('app.fields.status'))
            ->badge()
            ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
            ->formatStateUsing(fn (string $state): string => $state === 'active'
                ? __('app.statuses.active')
                : __('app.statuses.inactive'));
    }
}
