<?php

namespace App\Filament\Admin\Resources\DhikrResource\Pages;

use App\Filament\Admin\Resources\DhikrResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDhikrs extends ListRecords
{
    protected static string $resource = DhikrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
