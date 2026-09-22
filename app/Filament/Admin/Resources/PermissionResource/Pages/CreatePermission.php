<?php

namespace App\Filament\Admin\Resources\PermissionResource\Pages;

use App\Filament\Admin\Resources\PermissionResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = PermissionResource::class;
}
