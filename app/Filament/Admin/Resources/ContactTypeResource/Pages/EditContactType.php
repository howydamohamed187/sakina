<?php

namespace App\Filament\Admin\Resources\ContactTypeResource\Pages;

use App\Filament\Admin\Resources\ContactTypeResource;
use App\Filament\Concerns\HasAdminFormLayout;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditContactType extends EditRecord
{
    use HasAdminFormLayout;

    protected static string $resource = ContactTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label(__('app.actions.delete')),
            RestoreAction::make()->label(__('app.actions.restore')),
            ForceDeleteAction::make()->label(__('app.actions.force_delete')),
        ];
    }
}
