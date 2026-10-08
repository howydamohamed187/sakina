<?php

namespace App\Filament\Admin\Resources\RuqyahStepResource\Pages;

use App\Filament\Admin\Resources\RuqyahStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRuqyahSteps extends ListRecords
{
    protected static string $resource = RuqyahStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
