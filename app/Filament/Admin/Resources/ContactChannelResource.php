<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ContactChannelResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Filament\Concerns\HasTrash;
use App\Filament\Forms\AdminForm;
use App\Filament\Forms\Components\PhoneField;
use App\Models\ContactChannel;
use App\Models\ContactType;
use App\Support\ContactTypes;
use App\Support\Permissions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactChannelResource extends Resource
{
    use AuthorizesByPermission;
    use HasTrash;

    protected static ?string $model = ContactChannel::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_CONTACTS;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.zakat');
    }

    public static function getModelLabel(): string
    {
        return __('app.zakat_suggestion');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.zakat_suggestions');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.zakat_suggestions');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                AdminForm::section(__('app.admin_form.zakat_suggestion'), [
                    TextInput::make('name')
                        ->label(__('app.fields.name'))
                        ->required()
                        ->maxLength(255),
                    Select::make('contact_type_id')
                        ->label(__('app.fields.contact_type'))
                        ->relationship(
                            'contactType',
                            'name',
                            fn ($query) => $query->where('status', 'active')->orderBy('sort_order')
                        )
                        ->required()
                        ->native(false)
                        ->live()
                        ->searchable()
                        ->preload(),
                    TextInput::make('link_value')
                        ->label(__('app.fields.contact_link'))
                        ->url()
                        ->required(fn (Get $get): bool => self::kindOf($get('contact_type_id')) === ContactTypes::LINK)
                        ->maxLength(255)
                        ->visible(fn (Get $get): bool => self::kindOf($get('contact_type_id')) === ContactTypes::LINK)
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->columnSpanFull(),
                    TextInput::make('account_value')
                        ->label(__('app.fields.account_number'))
                        ->required(fn (Get $get): bool => self::kindOf($get('contact_type_id')) === ContactTypes::ACCOUNT)
                        ->maxLength(255)
                        ->visible(fn (Get $get): bool => self::kindOf($get('contact_type_id')) === ContactTypes::ACCOUNT)
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->columnSpanFull(),
                    PhoneField::make('phone_value')
                        ->label(fn (Get $get): string => ContactTypes::valueLabel(self::kindOf($get('contact_type_id'))))
                        ->required(fn (Get $get): bool => ContactTypes::isPhone(self::kindOf($get('contact_type_id'))))
                        ->withoutUnique()
                        ->visible(fn (Get $get): bool => ContactTypes::isPhone(self::kindOf($get('contact_type_id'))))
                        ->columnSpanFull(),
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
                TextColumn::make('contactType.name')
                    ->label(__('app.fields.contact_type'))
                    ->badge(),
                TextColumn::make('value')
                    ->label(__('app.fields.contact_value'))
                    ->formatStateUsing(fn (string $state, ContactChannel $record): string => $record->formattedValue())
                    ->url(fn (ContactChannel $record): ?string => $record->kind() === ContactTypes::LINK ? $record->value : null)
                    ->openUrlInNewTab()
                    ->searchable()
                    ->limit(40),
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
            'index' => Pages\ListContactChannels::route('/'),
            'create' => Pages\CreateContactChannel::route('/create'),
            'edit' => Pages\EditContactChannel::route('/{record}/edit'),
        ];
    }

    private static function kindOf(mixed $typeId): ?string
    {
        if (! $typeId) {
            return null;
        }

        return ContactType::query()->find($typeId)?->kind;
    }
}
