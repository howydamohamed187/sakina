<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DailyQuestionResource\Pages;
use App\Filament\Admin\Resources\DailyQuestionResource\RelationManagers\AnswersRelationManager;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Models\DailyQuestion;
use App\Support\Permissions;
use App\Support\QuestionCategories;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyQuestionResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = DailyQuestion::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_DAILY_QUESTIONS;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'body';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.daily_questions');
    }

    public static function getModelLabel(): string
    {
        return __('app.daily_question');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.daily_questions');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.daily_questions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.daily_question'), [
                    Textarea::make('body')
                        ->label(__('app.fields.question'))
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('category')
                        ->label(__('app.fields.category'))
                        ->options(QuestionCategories::options())
                        ->native(false)
                        ->columnSpanFull()
                        ->placeholder(__('app.fields.uncategorized')),

                    Repeater::make('options')
                        ->label(__('app.fields.answers'))
                        ->relationship()
                        ->schema([
                            Textarea::make('body')
                                ->label(__('app.fields.answer'))
                                ->required()
                                ->rows(2)
                                ->columnSpan(2),
                            Toggle::make('is_correct')
                                ->label(__('app.fields.correct_answer'))
                                ->inline(false),
                        ])
                        ->columns(3)
                        ->minItems(2)
                        ->maxItems(6)
                        ->defaultItems(4)
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->columnSpanFull()
                        ->helperText(__('app.helpers.one_correct_answer'))
                        ->rules([
                            fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                $correct = collect($value ?? [])
                                    ->filter(fn (mixed $row): bool => (bool) data_get($row, 'is_correct'))
                                    ->count();

                                if ($correct !== 1) {
                                    $fail(__('app.helpers.one_correct_answer'));
                                }
                            },
                        ]),
                        AdminForm::statusToggle(),
                ], 2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('app.admin_form.daily_question_answers'))
                    ->schema([
                        RepeatableEntry::make('options')
                            ->label(__('app.fields.answers'))
                            ->schema([
                                TextEntry::make('body')
                                    ->hiddenLabel()
                                    ->formatStateUsing(function (string $state, $record): string {
                                        return $state.(! empty($record?->is_correct) ? ' ✅' : '');
                                    }),
                            ])
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label(__('app.fields.status'))
                            ->badge()
                            ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                            ->formatStateUsing(fn (string $state): string => $state === 'active'
                                ? __('app.question_statuses.active')
                                : __('app.question_statuses.inactive')),
                        TextEntry::make('category')
                            ->label(__('app.fields.category'))
                            ->formatStateUsing(fn (?string $state): string => QuestionCategories::label($state)),
                        TextEntry::make('created_at')
                            ->label(__('app.fields.created_at'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with('options')
                ->withCount([
                    'assignments',
                    'answers',
                    'answers as correct_answers_count' => fn (Builder $query): Builder => $query->where('is_correct', true),
                ]))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('body')
                    ->label(__('app.fields.question'))
                    ->searchable()
                    ->limit(40)
                    ->wrap(),
                TextColumn::make('correct_answer')
                    ->label(__('app.fields.correct_answer'))
                    ->limit(30),
                TextColumn::make('answers_count')
                    ->label(__('app.fields.participations'))
                    ->sortable(),
                TextColumn::make('correct_answers_count')
                    ->label(__('app.fields.correct_answers_short'))
                    ->sortable(),
                TextColumn::make('success_rate')
                    ->label(__('app.fields.success_rate'))
                    ->state(fn (DailyQuestion $record): string => $record->successRateLabel()),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'active'
                        ? __('app.question_statuses.active')
                        : __('app.question_statuses.inactive')),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::trashFilter(),
            ])
            ->actions([
                ViewAction::make()->label(__('app.actions.view')),
                EditAction::make()->label(__('app.actions.edit')),
                Action::make('toggleStatus')
                    ->label(fn (DailyQuestion $record): string => $record->isActive()
                        ? __('app.actions.deactivate')
                        : __('app.actions.activate'))
                    ->icon(fn (DailyQuestion $record): string => $record->isActive()
                        ? 'heroicon-o-pause-circle'
                        : 'heroicon-o-play-circle')
                    ->color(fn (DailyQuestion $record): string => $record->isActive() ? 'warning' : 'success')
                    ->action(function (DailyQuestion $record): void {
                        $record->update([
                            'status' => $record->isActive() ? 'suspended' : 'active',
                        ]);
                    }),
                ...static::trashRecordActions(),
            ])
            ->bulkActions([
                static::trashBulkActions(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyQuestions::route('/'),
            'create' => Pages\CreateDailyQuestion::route('/create'),
            'view' => Pages\ViewDailyQuestion::route('/{record}'),
            'edit' => Pages\EditDailyQuestion::route('/{record}/edit'),
        ];
    }

}
