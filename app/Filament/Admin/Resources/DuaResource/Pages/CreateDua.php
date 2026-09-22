<?php

namespace App\Filament\Admin\Resources\DuaResource\Pages;

use App\Filament\Admin\Resources\DuaResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\Dua;
use Filament\Resources\Pages\CreateRecord;

class CreateDua extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = DuaResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) Dua::query()->max('sort_order') + 1;

        return $data;
    }
}
