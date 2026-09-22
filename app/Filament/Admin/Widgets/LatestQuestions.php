<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\DailyQuestionResource;
use App\Models\DailyQuestion;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestQuestions extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.dashboard_widgets.latest_questions'))
            ->query(DailyQuestion::query()->latest())
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->recordUrl(function (DailyQuestion $record): ?string {
                return DailyQuestionResource::canViewAny()
                    ? DailyQuestionResource::getUrl('view', ['record' => $record])
                    : null;
            })
            ->columns([
                TextColumn::make('body')
                    ->label(__('app.fields.question'))
                    ->limit(42)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->since(),
            ]);
    }
}
