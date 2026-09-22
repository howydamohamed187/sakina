<?php

namespace App\Filament\Admin\Resources\HadithResource\Pages;

use App\Filament\Admin\Resources\HadithResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\Hadith;
use Filament\Resources\Pages\CreateRecord;

class CreateHadith extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = HadithResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) Hadith::query()->max('sort_order') + 1;

        return $data;
    }
}
