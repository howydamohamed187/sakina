<?php

namespace App\Filament\Admin\Resources\DuaResource\Pages;

use App\Filament\Admin\Resources\DuaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDuas extends ListRecords
{
    protected static string $resource = DuaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
