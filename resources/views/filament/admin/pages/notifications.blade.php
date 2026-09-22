<x-filament-panels::page>
    @php($notifications = $this->getNotifications())
    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <x-filament::section>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $this->titleFor($notification) }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $this->bodyFor($notification) }}
                        </p>
                        <p class="mt-2 text-xs text-gray-400">
                            {{ $notification->created_at?->diffForHumans() }}
                            @if ($notification->read_at)
                                · {{ __('api.notification_read') }}
                            @endif
                        </p>
                    </div>
                    @unless ($notification->read_at)
                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="markAsRead('{{ $notification->id }}')"
                        >
                            {{ __('api.notification_read') }}
                        </x-filament::button>
                    @endunless
                </div>
            </x-filament::section>
        @empty
            <x-filament::section>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('app.notifications_empty') }}
                </p>
            </x-filament::section>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</x-filament-panels::page>
