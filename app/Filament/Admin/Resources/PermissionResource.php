<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PermissionResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Forms\AdminForm;
use App\Support\Permissions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Permission::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_ROLES;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.permission_management');
    }

    public static function getModelLabel(): string
    {
        return __('app.permission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.permissions');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.permissions');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            AdminForm::section(__('app.admin_form.permission'), [
                TextInput::make('name')
                    ->label(__('app.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('guard_name')
                    ->label(__('app.fields.guard_name'))
                    ->options([
                        'web' => 'web',
                    ])
                    ->default('web')
                    ->required()
                    ->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label(__('app.fields.guard_name'))
                    ->badge(),
                TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label(__('app.roles')),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()->label(__('app.actions.edit')),
                DeleteAction::make()->label(__('app.actions.delete')),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->label(__('app.actions.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
