<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DuaResource\Pages;
use App\Filament\Admin\Resources\DuaResource\RelationManagers\FavoritesRelationManager;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Models\Dua;
use App\Support\DuaCategories;
use App\Support\Permissions;
use Filament\Forms\Components\Select;
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
use Illuminate\Database\Eloquent\Builder;

class DuaResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = Dua::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_DUAS;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 60;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.duas');
    }

    public static function getModelLabel(): string
    {
        return __('app.dua');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.duas');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.duas');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.dua'), [
                    TextInput::make('title')
                        ->label(__('app.fields.dua_title'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('body')
                        ->label(__('app.fields.dua_body'))
                        ->required()
                        ->rows(6)
                        ->columnSpanFull(),
                    Select::make('category')
                        ->label(__('app.fields.dua_category'))
                        ->options(DuaCategories::options())
                        ->required()
                        ->native(false),
                    AdminForm::statusToggle(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('app.admin_form.dua'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('app.fields.dua_title')),
                        TextEntry::make('body')
                            ->label(__('app.fields.dua_body'))
                            ->columnSpanFull(),
                        TextEntry::make('category')
                            ->label(__('app.fields.dua_category'))
                            ->formatStateUsing(fn (?string $state): string => DuaCategories::label($state)),
                        TextEntry::make('favorites_count')
                            ->label(__('app.fields.favorite_count'))
                            ->state(fn (Dua $record): int => $record->favoritesCount()),
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
                    ->label(__('app.fields.dua_title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label(__('app.fields.dua_body'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('category')
                    ->label(__('app.fields.dua_category'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => DuaCategories::label($state))
                    ->sortable(),
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
                SelectFilter::make('category')
                    ->label(__('app.fields.dua_category'))
                    ->options(DuaCategories::options()),
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
            'index' => Pages\ListDuas::route('/'),
            'create' => Pages\CreateDua::route('/create'),
            'view' => Pages\ViewDua::route('/{record}'),
            'edit' => Pages\EditDua::route('/{record}/edit'),
        ];
    }
}
