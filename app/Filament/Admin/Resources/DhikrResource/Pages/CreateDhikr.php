<?php

namespace App\Filament\Admin\Resources\DhikrResource\Pages;

use App\Filament\Admin\Resources\DhikrResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\Dhikr;
use Filament\Resources\Pages\CreateRecord;

class CreateDhikr extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = DhikrResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) Dhikr::query()->max('sort_order') + 1;

        return $data;
    }
}
