<?php

namespace App\Filament\Admin\Resources\QuranReciterResource\Pages;

use App\Filament\Admin\Resources\QuranReciterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuranReciters extends ListRecords
{
    protected static string $resource = QuranReciterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
