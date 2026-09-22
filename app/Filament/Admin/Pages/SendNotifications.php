<?php

namespace App\Filament\Admin\Pages;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\AdminMessageNotification;
use App\Support\Locales;
use App\Support\Permissions;
use App\Support\Roles;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendNotifications extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'send-notifications';

    protected static string $view = 'filament.admin.pages.send-notifications';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('app.nav.notifications');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.send_notifications');
    }

    public function getTitle(): string|Htmlable
    {
        return __('app.send_notifications');
    }

    public function getHeading(): string|Htmlable
    {
        return __('app.send_notifications');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        return $user->can(Permissions::name(Permissions::RESOURCE_NOTIFICATIONS, Permissions::ACTION_CREATE));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'title' => [],
            'body' => [],
            'recipient_type' => 'customers',
            'notification_type' => 'all',
            'notifiable' => [],
        ]);
    }

    public function form(Form $form): Form
    {
        $tabs = collect(Locales::all())
            ->map(function (string $locale): Tab {
                $required = $locale === Locales::default();

                return Tab::make($locale)
                    ->label(Locales::label($locale))
                    ->schema([
                        TextInput::make("title.{$locale}")
                            ->label(__('app.notification_form.title'))
                            ->required($required)
                            ->maxLength(255),
                        Textarea::make("body.{$locale}")
                            ->label(__('app.notification_form.body'))
                            ->required($required)
                            ->rows(8),
                    ]);
            })
            ->all();

        return $form
            ->schema([
                Section::make(__('app.send_notifications'))
                    ->description(__('app.notification_form.description'))
                    ->schema([
                        Tabs::make('translations')
                            ->tabs($tabs)
                            ->contained(false)
                            ->columnSpanFull(),
                        Radio::make('recipient_type')
                            ->label(__('app.notification_form.recipient_type'))
                            ->options([
                                'customers' => __('app.customers'),
                                'admins' => __('app.admins'),
                            ])
                            ->inline()
                            ->live()
                            ->required(),
                        Radio::make('notification_type')
                            ->label(__('app.notification_form.type'))
                            ->options([
                                'all' => __('app.notification_form.all'),
                                'specific' => __('app.notification_form.specific'),
                            ])
                            ->inline()
                            ->live()
                            ->required(),
                        Select::make('notifiable')
                            ->label(__('app.notification_form.recipients'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->optionsLimit(200)
                            ->visible(fn (Get $get): bool => $get('notification_type') === 'specific')
                            ->required(fn (Get $get): bool => $get('notification_type') === 'specific')
                            ->options(fn (Get $get): array => $this->recipientOptions($get('recipient_type'))),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $recipients = $this->recipients($state);

        if ($recipients->isEmpty()) {
            FilamentNotification::make()
                ->title(__('app.notification_form.none'))
                ->warning()
                ->send();

            return;
        }

        Notification::send(
            $recipients,
            new AdminMessageNotification(
                $this->localizedValues($state['title'] ?? []),
                $this->localizedValues($state['body'] ?? []),
            ),
        );

        $this->form->fill([
            'title' => [],
            'body' => [],
            'recipient_type' => 'customers',
            'notification_type' => 'all',
            'notifiable' => [],
        ]);

        FilamentNotification::make()
            ->title(__('app.notification_form.sent', ['count' => $recipients->count()]))
            ->success()
            ->send();
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function recipients(array $state): Collection
    {
        $specific = ($state['notification_type'] ?? 'all') === 'specific';
        $ids = $state['notifiable'] ?? [];

        if (($state['recipient_type'] ?? 'customers') === 'admins') {
            $query = $this->adminQuery();

            if ($specific) {
                $query = User::query()->whereIn('id', $ids);
            }

            return $query->get();
        }

        $query = Customer::query();

        if ($specific) {
            $query->whereIn('id', $ids);
        } else {
            $query->where('status', 'active');
        }

        return $query->get();
    }

    /**
     * @return array<int|string, string>
     */
    protected function recipientOptions(?string $recipientType): array
    {
        if ($recipientType === 'admins') {
            return $this->formatRecipients(
                $this->adminQuery()
                    ->orderBy('name')
                    ->get(['id', 'name', 'email'])
            );
        }

        return $this->formatRecipients(
            Customer::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
        );
    }

    protected function adminQuery(): Builder
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', Roles::staff()));
    }

    /**
     * @return array<int|string, string>
     */
    protected function formatRecipients(Collection $records): array
    {
        return $records
            ->mapWithKeys(fn ($record): array => [
                $record->getKey() => trim($record->name.' — '.$record->email),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    protected function localizedValues(array $values): array
    {
        return collect($values)
            ->map(fn (mixed $value): string => is_scalar($value) ? trim((string) $value) : '')
            ->filter()
            ->all();
    }
}
