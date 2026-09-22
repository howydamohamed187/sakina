<?php

namespace App\Filament\Concerns;

use App\Support\Permissions;
use Spatie\Permission\Models\Role;

trait SyncsRolePermissionGroups
{
    /**
     * @var array<string, mixed>
     */
    protected array $permissionGroups = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractPermissionGroups(array $data): array
    {
        $this->permissionGroups = collect(Permissions::GROUPS)
            ->mapWithKeys(fn (string $group): array => [
                $group => $this->data['permission_groups'][$group] ?? $data['permission_groups'][$group] ?? [],
            ])
            ->all();

        unset($data['permission_groups']);

        return $data;
    }

    protected function syncExtractedPermissionGroups(): void
    {
        if ($this->record instanceof Role) {
            Permissions::syncGroups($this->record, $this->permissionGroups);
        }
    }
}
