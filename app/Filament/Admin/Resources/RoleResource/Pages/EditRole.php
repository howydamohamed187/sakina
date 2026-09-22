<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use App\Filament\Admin\Resources\RoleResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\SyncsRolePermissionGroups;
use App\Support\Permissions;
use App\Support\Roles;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditRole extends EditRecord
{
    use HasAdminFormLayout;
    use SyncsRolePermissionGroups;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('app.actions.delete'))
                ->disabled(fn (): bool => in_array($this->record->name, Roles::system(), true)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record instanceof Role) {
            $this->record->loadMissing('permissions');
            $data['permission_groups'] = Permissions::groupedState($this->record);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record instanceof Role && in_array($this->record->name, Roles::system(), true)) {
            $data['name'] = $this->record->name;
        }

        return $this->extractPermissionGroups($data);
    }

    protected function afterSave(): void
    {
        $this->syncExtractedPermissionGroups();
    }
}
