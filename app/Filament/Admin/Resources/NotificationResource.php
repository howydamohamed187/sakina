<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NotificationResource\Pages;
use App\Filament\Concerns\AuthorizesByPermission;
use App\Support\NotificationMessage;
use App\Support\Permissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = DatabaseNotification::class;

    protected static ?string $permissionResource = Permissions::RESOURCE_NOTIFICATIONS;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'notifications';

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.notifications');
    }

    public static function getModelLabel(): string
    {
        return __('app.notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.my_notifications');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.my_notifications');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('app.notification_form.title'))
                    ->getStateUsing(fn (DatabaseNotification $record): string => NotificationMessage::title($record))
                    ->description(fn (DatabaseNotification $record): string => NotificationMessage::body($record))
                    ->wrap()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('data', 'like', '%'.$search.'%')),
                TextColumn::make('read_at')
                    ->label(__('app.notification_form.status'))
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state
                        ? __('app.notification_form.read')
                        : __('app.notification_form.unread'))
                    ->color(fn (mixed $state): string => $state ? 'gray' : 'warning'),
                TextColumn::make('created_at')
                    ->label(__('app.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                DeleteAction::make()->label(__('app.actions.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('app.actions.delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->where('notifiable_id', $user?->getKey())
            ->where('notifiable_type', $user?->getMorphClass());
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
