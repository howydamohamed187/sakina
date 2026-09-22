<?php

namespace App\Filament\Admin\Resources\HadithResource\Pages;

use App\Filament\Admin\Resources\HadithResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHadiths extends ListRecords
{
    protected static string $resource = HadithResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
