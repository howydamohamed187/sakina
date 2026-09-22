<?php

namespace App\Filament\Admin\Resources\DailyQuestionResource\Pages;

use App\Filament\Admin\Resources\DailyQuestionResource;
use App\Models\DailyQuestion;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewDailyQuestion extends ViewRecord
{
    protected static string $resource = DailyQuestionResource::class;

    protected static string $view = 'filament.admin.resources.daily-questions.view';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        /** @var DailyQuestion $record */
        $record = $this->getRecord();

        return [
            EditAction::make()->label(__('app.actions.edit')),
            Action::make('toggleStatus')
                ->label($record->isActive()
                    ? __('app.actions.deactivate')
                    : __('app.actions.activate'))
                ->icon($record->isActive() ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                ->color($record->isActive() ? 'warning' : 'success')
                ->action(function (): void {
                    /** @var DailyQuestion $record */
                    $record = $this->getRecord();
                    $record->update([
                        'status' => $record->isActive() ? 'suspended' : 'active',
                    ]);
                }),
            DeleteAction::make()->label(__('app.actions.delete')),
            RestoreAction::make()->label(__('app.actions.restore')),
            ForceDeleteAction::make()->label(__('app.actions.force_delete')),
        ];
    }
}
