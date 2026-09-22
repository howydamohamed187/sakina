<?php

namespace App\Filament\Admin\Resources\CustomerResource\RelationManagers;

use App\Support\DuaCategories;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FavoriteDuasRelationManager extends RelationManager
{
    protected static string $relationship = 'duaFavorites';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.favorite_duas');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('dua.title')
                    ->label(__('app.fields.dua_title'))
                    ->searchable(),
                TextColumn::make('dua.category')
                    ->label(__('app.fields.dua_category'))
                    ->formatStateUsing(fn (?string $state): string => DuaCategories::label($state)),
                TextColumn::make('created_at')
                    ->label(__('app.fields.favorited_at'))
                    ->date('d/m/Y'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('app.favorite_duas_empty'));
    }
}
