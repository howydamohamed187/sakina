<?php

namespace App\Filament\Admin\Resources\RuqyahStepResource\Pages;

use App\Filament\Admin\Resources\RuqyahStepResource;
use App\Filament\Concerns\HasAdminFormLayout;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRuqyahStep extends EditRecord
{
    use HasAdminFormLayout;

    protected static string $resource = RuqyahStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label(__('app.actions.view')),
            DeleteAction::make()->label(__('app.actions.delete')),
            RestoreAction::make()->label(__('app.actions.restore')),
            ForceDeleteAction::make()->label(__('app.actions.force_delete')),
        ];
    }
}
