@php
    $current = app()->getLocale();
@endphp

<div class="fi-locale-switcher">
    <label class="sr-only" for="locale-switcher">{{ __('app.language') }}</label>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 start-2.5 flex items-center text-gray-400">
            <x-filament::icon icon="heroicon-m-language" class="h-4 w-4" />
        </span>
        <select
            id="locale-switcher"
            class="fi-input block h-9 min-w-[8.5rem] rounded-lg border-none bg-gray-50 pe-8 ps-8 text-sm text-gray-950 shadow-sm outline-none ring-1 ring-gray-950/10 transition duration-75 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
            onchange="window.location.href = this.value"
        >
            @foreach (\App\Support\Locales::all() as $locale)
                <option
                    value="{{ route('locale.switch', ['locale' => $locale]) }}"
                    @selected($locale === $current)
                >
                    {{ \App\Support\Locales::label($locale) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
