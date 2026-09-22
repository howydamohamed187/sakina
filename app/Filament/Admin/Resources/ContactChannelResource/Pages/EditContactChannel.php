<?php

namespace App\Filament\Admin\Resources\ContactChannelResource\Pages;

use App\Filament\Admin\Resources\ContactChannelResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\SyncsContactValue;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditContactChannel extends EditRecord
{
    use HasAdminFormLayout;
    use SyncsContactValue;

    protected static string $resource = ContactChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label(__('app.actions.delete')),
            RestoreAction::make()->label(__('app.actions.restore')),
            ForceDeleteAction::make()->label(__('app.actions.force_delete')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mergeContactValue($data);
    }
}
