<?php

namespace App\Filament\Admin\Resources\QuranSurahResource\Pages;

use App\Filament\Admin\Resources\QuranSurahResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewQuranSurah extends ViewRecord
{
    protected static string $resource = QuranSurahResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label(__('app.actions.edit')),
        ];
    }
}
