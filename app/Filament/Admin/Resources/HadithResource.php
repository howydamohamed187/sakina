<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HadithResource\Pages;
use App\Filament\Admin\Resources\HadithResource\RelationManagers\FavoritesRelationManager;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Models\Hadith;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HadithResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = Hadith::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_HADITHS;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.hadiths');
    }

    public static function getModelLabel(): string
    {
        return __('app.hadith');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.hadiths');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.hadiths');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.hadith'), [
                    TextInput::make('title')
                        ->label(__('app.fields.hadith_title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('body')
                        ->label(__('app.fields.hadith_body'))
                        ->required()
                        ->rows(6)
                        ->columnSpanFull(),
                    AdminForm::statusToggle()->columnSpanFull(),
                ], 1),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('app.admin_form.hadith'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('app.fields.hadith_title')),
                        TextEntry::make('body')
                            ->label(__('app.fields.hadith_body'))
                            ->columnSpanFull(),
                        TextEntry::make('favorites_count')
                            ->label(__('app.fields.favorite_count'))
                            ->state(fn (Hadith $record): int => $record->favoritesCount()),
                        TextEntry::make('status')
                            ->label(__('app.fields.status'))
                            ->badge()
                            ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                            ->formatStateUsing(fn (string $state): string => $state === 'active'
                                ? __('app.question_statuses.active')
                                : __('app.question_statuses.inactive')),
                        TextEntry::make('created_at')
                            ->label(__('app.fields.created_at'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('favorites'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label(__('app.fields.hadith_title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label(__('app.fields.hadith_body'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('favorites_count')
                    ->label(__('app.fields.favorite_count'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'active'
                        ? __('app.question_statuses.active')
                        : __('app.question_statuses.inactive')),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::trashFilter(),
            ])
            ->actions([
                ViewAction::make()->label(__('app.actions.view')),
                EditAction::make()->label(__('app.actions.edit')),
                ...static::trashRecordActions(),
            ])
            ->bulkActions([
                static::trashBulkActions(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FavoritesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHadiths::route('/'),
            'create' => Pages\CreateHadith::route('/create'),
            'view' => Pages\ViewHadith::route('/{record}'),
            'edit' => Pages\EditHadith::route('/{record}/edit'),
        ];
    }
}
