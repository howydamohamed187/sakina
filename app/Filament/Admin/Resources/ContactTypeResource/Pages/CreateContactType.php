<?php

namespace App\Filament\Admin\Resources\ContactTypeResource\Pages;

use App\Filament\Admin\Resources\ContactTypeResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\ContactType;
use Filament\Resources\Pages\CreateRecord;

class CreateContactType extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = ContactTypeResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) ContactType::query()->max('sort_order') + 1;

        return $data;
    }
}
