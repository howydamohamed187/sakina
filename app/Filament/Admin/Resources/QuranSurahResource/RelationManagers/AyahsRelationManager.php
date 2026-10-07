<?php

namespace App\Filament\Admin\Resources\QuranSurahResource\RelationManagers;

use App\Models\QuranAyah;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AyahsRelationManager extends RelationManager
{
    protected static string $relationship = 'ayahs';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.quran_admin.ayahs');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('aya')
            ->defaultSort('aya')
            ->paginated([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('aya')
                    ->label(__('app.quran_admin.fields.ayah_number'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('verse_key')
                    ->label(__('app.quran_admin.fields.verse_key'))
                    ->state(fn (QuranAyah $record): string => $record->verseKey()),
                TextColumn::make('text')
                    ->label(__('app.quran_admin.fields.text'))
                    ->wrap()
                    ->size(TextColumn\TextColumnSize::Large)
                    ->extraAttributes(['dir' => 'rtl', 'lang' => 'ar'])
                    ->searchable(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
