<?php

namespace App\Filament\Admin\Resources\DailyQuestionResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('app.daily_question_answers');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('answered_at', 'desc')
            ->columns([
                TextColumn::make('customer.name')
                    ->label(__('app.fields.user'))
                    ->searchable(),
                TextColumn::make('option_body')
                    ->label(__('app.fields.selected_answer'))
                    ->limit(40),
                TextColumn::make('is_correct')
                    ->label(__('app.fields.result'))
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state
                        ? __('app.daily_question_result.correct')
                        : __('app.daily_question_result.wrong')),
                TextColumn::make('answered_at')
                    ->label(__('app.fields.answered_at'))
                    ->date('d/m/Y'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading(__('app.daily_question_answers_empty'));
    }
}
