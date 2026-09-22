<?php

namespace App\Filament\Admin\Resources\DuaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FavoritesRelationManager extends RelationManager
{
    protected static string $relationship = 'favorites';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.dua_favorites');
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
                TextColumn::make('customer.name')
                    ->label(__('app.fields.user'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('app.fields.favorited_at'))
                    ->date('d/m/Y'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('app.dua_favorites_empty'));
    }
}
