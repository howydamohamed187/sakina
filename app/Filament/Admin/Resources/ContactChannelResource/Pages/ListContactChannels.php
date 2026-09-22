<?php

namespace App\Filament\Admin\Resources\ContactChannelResource\Pages;

use App\Filament\Admin\Resources\ContactChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContactChannels extends ListRecords
{
    protected static string $resource = ContactChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
