<?php

namespace App\Filament\Admin\Resources\ContactTypeResource\Pages;

use App\Filament\Admin\Resources\ContactTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactTypes extends ListRecords
{
    protected static string $resource = ContactTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
