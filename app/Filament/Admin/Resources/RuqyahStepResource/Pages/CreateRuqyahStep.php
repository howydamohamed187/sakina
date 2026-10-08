<?php

namespace App\Filament\Admin\Resources\RuqyahStepResource\Pages;

use App\Filament\Admin\Resources\RuqyahStepResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\RuqyahStep;
use Filament\Resources\Pages\CreateRecord;

class CreateRuqyahStep extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = RuqyahStepResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) RuqyahStep::withTrashed()->max('sort_order') + 1;

        return $data;
    }
}
