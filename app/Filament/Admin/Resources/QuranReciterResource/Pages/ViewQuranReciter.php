<?php

namespace App\Filament\Admin\Resources\QuranReciterResource\Pages;

use App\Filament\Admin\Resources\QuranReciterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuranReciter extends ViewRecord
{
    protected static string $resource = QuranReciterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label(__('app.actions.edit')),
            DeleteAction::make()->label(__('app.actions.delete')),
        ];
    }
}
