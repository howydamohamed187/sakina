<?php

namespace App\Filament\Admin\Resources\QuranReciterResource\Pages;

use App\Filament\Admin\Resources\QuranReciterResource;
use App\Filament\Concerns\HasAdminFormLayout;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuranReciter extends EditRecord
{
    use HasAdminFormLayout;

    protected static string $resource = QuranReciterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label(__('app.actions.view')),
            DeleteAction::make()->label(__('app.actions.delete')),
        ];
    }
}
