<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers\FavoriteDhikrsRelationManager;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers\FavoriteDuasRelationManager;
use App\Filament\Admin\Resources\CustomerResource\RelationManagers\FavoriteHadithsRelationManager;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Filament\Forms\Components\GoogleMapLocation;
use App\Models\Customer;
use App\Rules\TripleName;
use App\Support\Permissions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = Customer::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_CUSTOMERS;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('app.customer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.customers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.customer'), [
                    AdminForm::avatar('customers'),
                    TextInput::make('name')
                        ->label(__('app.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->rules([new TripleName])
                        ->helperText(__('validation.triple_name', ['attribute' => __('app.fields.name')])),
                    TextInput::make('email')
                        ->label(__('app.fields.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('location')
                        ->label(__('app.fields.address'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('app.helpers.customer_address'))
                        ->columnSpanFull(),
                    GoogleMapLocation::make('map_location')
                        ->label(__('app.fields.location')),
                    AdminForm::password()
                        ->live()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->helperText(fn (string $operation): ?string => $operation === 'create'
                            ? null
                            : __('app.profile_password_hint')),
                    AdminForm::passwordConfirmation(),
                    AdminForm::statusToggle()->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->label(__('app.fields.address'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->sortable()
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
                    ->sortable()
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

    public static function getRelations(): array
    {
        return [
            FavoriteHadithsRelationManager::class,
            FavoriteDhikrsRelationManager::class,
            FavoriteDuasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
