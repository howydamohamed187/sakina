<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Forms\AdminForm;
use App\Models\Customer;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Role::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_ROLES;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.permission_management');
    }

    public static function getModelLabel(): string
    {
        return __('app.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.roles');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.roles');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            AdminForm::section(__('app.admin_form.role_basics'), [
                TextInput::make('name')
                    ->label(__('app.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?Role $record): bool => $record !== null && in_array($record->name, Roles::system(), true)),
                Select::make('users')
                    ->label(__('app.fields.users'))
                    ->relationship(
                        'users',
                        'name',
                        fn (Builder $query): Builder => $query->orderBy('name'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => $record->name.' ('.$record->email.')')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->visible(fn (?Role $record): bool => $record === null || ! Roles::isCustomer($record->name))
                    ->columnSpanFull(),
            ])->description(__('app.admin_form.role_basics_hint')),
            AdminForm::section(__('app.admin_form.role_permissions'), static::permissionGroupFields(), 1)
                ->description(__('app.admin_form.role_permissions_hint')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('type')
                    ->label(__('app.fields.role_type'))
                    ->state(fn (Role $record): string => Roles::typeLabel($record->name))
                    ->badge()
                    ->color(fn (Role $record): string => Roles::isCustomer($record->name) ? 'success' : 'danger'),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label(__('app.fields.permissions'))
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state): string => __('app.permissions_count', ['count' => $state])),
                TextColumn::make('users_count')
                    ->label(__('app.fields.users'))
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function (Role $record): int {
                        if (Roles::isCustomer($record->name)) {
                            return Customer::query()->role($record->name)->count();
                        }

                        return (int) ($record->users_count ?? $record->users()->count());
                    })
                    ->formatStateUsing(fn ($state): string => __('app.users_count', ['count' => $state])),
            ])
            ->actions([
                EditAction::make()->label(__('app.actions.edit')),
                DeleteAction::make()
                    ->label(__('app.actions.delete'))
                    ->disabled(fn (Role $record): bool => in_array($record->name, Roles::system(), true)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label(__('app.actions.delete'))
                    ->action(function (Collection $records): void {
                        $records
                            ->reject(fn (Role $role): bool => in_array($role->name, Roles::system(), true))
                            ->each(fn (Role $role) => $role->delete());
                    }),
            ]);
    }

    /**
     * @return array<int, Tabs>
     */
    public static function permissionGroupFields(): array
    {
        return [
            Tabs::make('permission_groups')
                ->contained(false)
                ->tabs(
                    collect(Permissions::GROUPS)
                        ->map(fn (string $group): Tab => Tab::make(__('app.permission_groups.'.$group))
                            ->schema([
                                CheckboxList::make('permission_groups.'.$group)
                                    ->hiddenLabel()
                                    ->options(fn (): array => Permissions::optionsForGroup($group))
                                    ->searchable()
                                    ->searchPrompt(__('app.fields.search_permissions'))
                                    ->bulkToggleable()
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 4,
                                    ])
                                    ->extraAttributes(['class' => 'fi-fo-role-permissions']),
                            ]))
                        ->all()
                )
                ->columnSpanFull(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount(['permissions', 'users']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
