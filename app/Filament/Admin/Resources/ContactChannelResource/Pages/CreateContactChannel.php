<?php

namespace App\Filament\Admin\Resources\ContactChannelResource\Pages;

use App\Filament\Admin\Resources\ContactChannelResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Filament\Concerns\SyncsContactValue;
use App\Models\ContactChannel;
use Filament\Resources\Pages\CreateRecord;

class CreateContactChannel extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;
    use SyncsContactValue;

    protected static string $resource = ContactChannelResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) ContactChannel::query()->max('sort_order') + 1;

        return $this->mergeContactValue($data);
    }
}
