<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QuranSurahResource\Pages;
use App\Filament\Admin\Resources\QuranSurahResource\RelationManagers\AyahsRelationManager;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Forms\AdminForm;
use App\Models\QuranSurah;
use App\Support\Permissions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Surahs are browsable; only the separate "information" metadata is editable. Quran text is never editable.
 */
class QuranSurahResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = QuranSurah::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_QURAN;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 70;

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.quran');
    }

    public static function getModelLabel(): string
    {
        return __('app.quran_admin.surah');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.quran_admin.surahs');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.quran_admin.surahs');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canReorder(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.quran_admin.surah_details'), [
                    TextInput::make('id')
                        ->label(__('app.quran_admin.fields.number'))
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('name_ar')
                        ->label(__('app.quran_admin.fields.name_ar'))
                        ->disabled()
                        ->dehydrated(false),
                ])->description(__('app.quran_admin.read_only_notice')),
                AdminForm::section(__('app.quran_admin.surah_information'), [
                    Textarea::make('information.ar')
                        ->label(__('app.quran_admin.fields.information_ar'))
                        ->rows(5),
                    Textarea::make('information.en')
                        ->label(__('app.quran_admin.fields.information_en'))
                        ->rows(5),
                    Textarea::make('information.ckb')
                        ->label(__('app.quran_admin.fields.information_ckb'))
                        ->rows(5),
                ], 1),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('app.quran_admin.surah_details'))
                    ->schema([
                        TextEntry::make('id')->label(__('app.quran_admin.fields.number')),
                        TextEntry::make('name_ar')->label(__('app.quran_admin.fields.name_ar')),
                        TextEntry::make('name_en')->label(__('app.quran_admin.fields.name_en')),
                        TextEntry::make('revelation_type')
                            ->label(__('app.quran_admin.fields.revelation_type'))
                            ->badge()
                            ->formatStateUsing(fn (QuranSurah $record): string => $record->revelationTypeLabel()),
                        TextEntry::make('ayahs_count')->label(__('app.quran_admin.fields.ayahs_count')),
                        TextEntry::make('read_only')
                            ->hiddenLabel()
                            ->state(__('app.quran_admin.read_only_notice'))
                            ->color('gray')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                InfoSection::make(__('app.quran_admin.surah_information'))
                    ->schema([
                        TextEntry::make('information.ar')->label(__('app.quran_admin.fields.information_ar'))->placeholder('—'),
                        TextEntry::make('information.en')->label(__('app.quran_admin.fields.information_en'))->placeholder('—'),
                        TextEntry::make('information.ckb')->label(__('app.quran_admin.fields.information_ckb'))->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->paginated([25, 50, 114])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                    ->label(__('app.quran_admin.fields.number'))
                    ->sortable(),
                TextColumn::make('name_ar')
                    ->label(__('app.quran_admin.fields.name_ar'))
                    ->searchable(),
                TextColumn::make('name_en')
                    ->label(__('app.quran_admin.fields.name_en'))
                    ->searchable(),
                TextColumn::make('revelation_type')
                    ->label(__('app.quran_admin.fields.revelation_type'))
                    ->badge()
                    ->color(fn (string $state): string => $state === QuranSurah::MECCAN ? 'warning' : 'success')
                    ->formatStateUsing(fn (QuranSurah $record): string => $record->revelationTypeLabel()),
                TextColumn::make('ayahs_count')
                    ->label(__('app.quran_admin.fields.ayahs_count'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('revelation_type')
                    ->label(__('app.quran_admin.fields.revelation_type'))
                    ->options([
                        QuranSurah::MECCAN => __('api.quran.revelation_types.meccan'),
                        QuranSurah::MEDINAN => __('api.quran.revelation_types.medinan'),
                    ]),
            ])
            ->actions([
                ViewAction::make()->label(__('app.actions.view')),
                EditAction::make()->label(__('app.actions.edit')),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            AyahsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuranSurahs::route('/'),
            'view' => Pages\ViewQuranSurah::route('/{record}'),
            'edit' => Pages\EditQuranSurah::route('/{record}/edit'),
        ];
    }
}
