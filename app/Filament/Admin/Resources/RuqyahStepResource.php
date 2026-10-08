<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RuqyahStepResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Models\RuqyahStep;
use App\Support\Permissions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RuqyahStepResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = RuqyahStep::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_RUQYAH;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 70;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.ruqyah');
    }

    public static function getModelLabel(): string
    {
        return __('app.ruqyah_step');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.ruqyah_steps');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.ruqyah_steps');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.ruqyah_step'), [
                    TextInput::make('title')
                        ->label(__('app.ruqyah_admin.fields.step_title'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('repeat_count')
                        ->label(__('app.ruqyah_admin.fields.repeat_count'))
                        ->numeric()
                        ->integer()
                        ->required()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(1000),
                    TextInput::make('instruction')
                        ->label(__('app.ruqyah_admin.fields.instruction'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('content')
                        ->label(__('app.ruqyah_admin.fields.content'))
                        ->required()
                        ->rows(10)
                        ->columnSpanFull(),
                    AdminForm::statusToggle(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make(__('app.admin_form.ruqyah_step'))
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('app.ruqyah_admin.fields.step_title')),
                        TextEntry::make('repeat_count')
                            ->label(__('app.ruqyah_admin.fields.repeat_count')),
                        TextEntry::make('instruction')
                            ->label(__('app.ruqyah_admin.fields.instruction'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('content')
                            ->label(__('app.ruqyah_admin.fields.content'))
                            ->columnSpanFull(),
                        TextEntry::make('sort_order')
                            ->label(__('app.ruqyah_admin.fields.sort_order')),
                        TextEntry::make('status')
                            ->label(__('app.fields.status'))
                            ->badge()
                            ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                            ->formatStateUsing(fn (string $state): string => $state === 'active'
                                ? __('app.question_statuses.active')
                                : __('app.question_statuses.inactive')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label(__('app.ruqyah_admin.fields.sort_order'))
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('app.ruqyah_admin.fields.step_title'))
                    ->searchable(),
                TextColumn::make('instruction')
                    ->label(__('app.ruqyah_admin.fields.instruction'))
                    ->limit(40),
                TextColumn::make('repeat_count')
                    ->label(__('app.ruqyah_admin.fields.repeat_count'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'active'
                        ? __('app.question_statuses.active')
                        : __('app.question_statuses.inactive')),
            ])
            ->filters([
                static::trashFilter(),
            ])
            ->actions([
                ViewAction::make()->label(__('app.actions.view')),
                EditAction::make()->label(__('app.actions.edit')),
                ...static::trashRecordActions(),
            ])
            ->bulkActions([
                static::trashBulkActions(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRuqyahSteps::route('/'),
            'create' => Pages\CreateRuqyahStep::route('/create'),
            'view' => Pages\ViewRuqyahStep::route('/{record}'),
            'edit' => Pages\EditRuqyahStep::route('/{record}/edit'),
        ];
    }
}
