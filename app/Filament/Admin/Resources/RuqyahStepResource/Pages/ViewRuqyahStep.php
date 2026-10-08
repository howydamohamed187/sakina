<?php

namespace App\Filament\Admin\Resources\RuqyahStepResource\Pages;

use App\Filament\Admin\Resources\RuqyahStepResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewRuqyahStep extends ViewRecord
{
    protected static string $resource = RuqyahStepResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label(__('app.actions.edit')),
            DeleteAction::make()->label(__('app.actions.delete')),
            RestoreAction::make()->label(__('app.actions.restore')),
            ForceDeleteAction::make()->label(__('app.actions.force_delete')),
        ];
    }
}
