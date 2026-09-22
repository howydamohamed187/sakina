<?php

namespace App\Filament\Admin\Resources\DailyQuestionResource\Pages;

use App\Filament\Admin\Resources\DailyQuestionResource;
use App\Filament\Concerns\HasAdminFormLayout;
use App\Filament\Concerns\RedirectsToListAfterCreate;
use App\Models\DailyQuestion;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyQuestion extends CreateRecord
{
    use HasAdminFormLayout;
    use RedirectsToListAfterCreate;

    protected static string $resource = DailyQuestionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sort_order'] = (int) DailyQuestion::query()->max('sort_order') + 1;

        return $data;
    }
}
