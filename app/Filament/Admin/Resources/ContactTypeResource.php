<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ContactTypeResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Models\ContactType;
use App\Support\ContactTypes;
use App\Support\Permissions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactTypeResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = ContactType::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_CONTACT_TYPES;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.zakat');
    }

    public static function getModelLabel(): string
    {
        return __('app.contact_type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.contact_type_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.contact_type_plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.contact_type'), [
                    TextInput::make('name')
                        ->label(__('app.fields.name'))
                        ->required()
                        ->maxLength(255),
                    Select::make('kind')
                        ->label(__('app.fields.contact_kind'))
                        ->options(ContactTypes::options())
                        ->required()
                        ->native(false)
                        ->helperText(__('app.helpers.contact_kind')),
                    AdminForm::statusToggle(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kind')
                    ->label(__('app.fields.contact_kind'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ContactTypes::label($state)),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'active'
                        ? __('app.statuses.active')
                        : __('app.statuses.inactive')),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(__('app.fields.deleted_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                static::trashFilter(),
            ])
            ->actions([
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
            'index' => Pages\ListContactTypes::route('/'),
            'create' => Pages\CreateContactType::route('/create'),
            'edit' => Pages\EditContactType::route('/{record}/edit'),
        ];
    }
}
