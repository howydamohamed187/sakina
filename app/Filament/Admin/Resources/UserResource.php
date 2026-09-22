<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Filament\Forms\PhoneInput;
use App\Models\User;
use App\Support\Permissions;
use App\Support\PhoneNumber;
use App\Support\Roles;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = User::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_ADMINS;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('app.admin');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.admins');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.admins');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.details'), [
                    AdminForm::avatar('avatars'),
                    TextInput::make('name')
                        ->label(__('app.fields.name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label(__('app.fields.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    PhoneInput::make(required: false),
                    AdminForm::password(),
                    Select::make('roles')
                        ->label(__('app.fields.role'))
                        ->relationship(
                            'roles',
                            'name',
                            fn ($query) => $query->where('name', '!=', Roles::CUSTOMER)
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record): string => Roles::label($record->name))
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required()->columnSpanFull(),
                    AdminForm::statusToggle(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(__('app.fields.avatar'))
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('app.fields.email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('app.fields.phone'))
                    ->formatStateUsing(fn (?string $state): ?string => PhoneNumber::format($state))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('app.fields.role'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Roles::label($state)),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
