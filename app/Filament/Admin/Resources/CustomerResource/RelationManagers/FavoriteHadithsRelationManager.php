<?php

namespace App\Filament\Admin\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FavoriteHadithsRelationManager extends RelationManager
{
    protected static string $relationship = 'hadithFavorites';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.favorite_hadiths');
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
                TextColumn::make('hadith.title')
                    ->label(__('app.fields.hadith_title'))
                    ->searchable(),
                TextColumn::make('hadith.body')
                    ->label(__('app.fields.hadith_body'))
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label(__('app.fields.favorited_at'))
                    ->date('d/m/Y'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('app.favorite_hadiths_empty'));
    }
}
