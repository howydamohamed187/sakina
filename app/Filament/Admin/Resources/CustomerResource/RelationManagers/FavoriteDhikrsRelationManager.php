<?php

namespace App\Filament\Admin\Resources\CustomerResource\RelationManagers;

use App\Support\DhikrCategories;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FavoriteDhikrsRelationManager extends RelationManager
{
    protected static string $relationship = 'dhikrFavorites';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.favorite_adhkar');
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
                TextColumn::make('dhikr.title')
                    ->label(__('app.fields.dhikr_title'))
                    ->searchable(),
                TextColumn::make('dhikr.category')
                    ->label(__('app.fields.dhikr_category'))
                    ->formatStateUsing(fn (?string $state): string => DhikrCategories::label($state)),
                TextColumn::make('created_at')
                    ->label(__('app.fields.favorited_at'))
                    ->date('d/m/Y'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('app.favorite_adhkar_empty'));
    }
}
