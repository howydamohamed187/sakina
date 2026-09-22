<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $statePath = $getStatePath();
        $isDisabled = $isDisabled();
        $id = $getId();
    @endphp

    <div
        class="fi-fo-phone-combined flex overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        dir="ltr"
    >
        <select
            id="{{ $id }}-country"
            {{ $applyStateBindingModifiers('wire:model.live') }}="{{ $statePath }}.country"
            @disabled($isDisabled)
            class="fi-fo-phone-country max-w-[8.5rem] shrink-0 border-0 bg-transparent py-1.5 pe-7 ps-3 text-sm text-gray-950 outline-none focus:ring-0 dark:text-white"
        >
            @foreach ($getCountryOptions() as $iso => $label)
                <option value="{{ $iso }}">{{ $label }}</option>
            @endforeach
        </select>

        <span class="my-2 w-px shrink-0 bg-gray-200 dark:bg-white/10"></span>

        <input
            id="{{ $id }}"
            type="tel"
            inputmode="numeric"
            {{ $applyStateBindingModifiers('wire:model.blur') }}="{{ $statePath }}.national"
            placeholder="{{ $getPlaceholder() }}"
            @disabled($isDisabled)
            autocomplete="tel-national"
            class="fi-input block w-full border-none bg-transparent py-1.5 ps-3 pe-3 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 disabled:cursor-not-allowed disabled:opacity-70 dark:text-white dark:placeholder:text-gray-500"
        />
    </div>
</x-dynamic-component>
