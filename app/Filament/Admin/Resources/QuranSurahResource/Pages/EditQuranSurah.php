<?php

namespace App\Filament\Admin\Resources\QuranSurahResource\Pages;

use App\Filament\Admin\Resources\QuranSurahResource;
use App\Filament\Concerns\HasAdminFormLayout;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuranSurah extends EditRecord
{
    use HasAdminFormLayout;

    protected static string $resource = QuranSurahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label(__('app.actions.view')),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ['information' => array_filter($data['information'] ?? [], 'filled') ?: null];
    }
}
