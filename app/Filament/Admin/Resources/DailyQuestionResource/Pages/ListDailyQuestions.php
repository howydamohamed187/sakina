<?php

namespace App\Filament\Admin\Resources\DailyQuestionResource\Pages;

use App\Filament\Admin\Resources\DailyQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyQuestions extends ListRecords
{
    protected static string $resource = DailyQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.create')),
        ];
    }
}
