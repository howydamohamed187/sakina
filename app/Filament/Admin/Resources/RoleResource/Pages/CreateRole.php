<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use App\Filament\Admin\Resources\RoleResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Filament\Concerns\SyncsRolePermissionGroups;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;
    use SyncsRolePermissionGroups;

    protected static string $resource = RoleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] ??= 'web';

        return $this->extractPermissionGroups($data);
    }

    protected function afterCreate(): void
    {
        $this->syncExtractedPermissionGroups();
    }
}
