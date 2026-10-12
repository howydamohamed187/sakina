<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $statePath = $getStatePath();
        $id = $getId();
        $isDisabled = $isDisabled();
    @endphp

    <div class="fi-fo-locked-secret" dir="ltr">
        <input
            id="{{ $id }}"
            type="text"
            {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}"
            value="{{ $getState() }}"
            @disabled($isDisabled)
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            spellcheck="false"
            data-lpignore="true"
            data-1p-ignore
            placeholder="{{ __('settings.helpers.secret_enter') }}"
            oncopy="return false"
            oncut="return false"
            oncontextmenu="return false"
            ondragstart="return false"
            onkeydown="if ((event.ctrlKey || event.metaKey) && ['c','C','x','X','Insert'].includes(event.key)) { event.preventDefault(); return false; }"
            class="fi-fo-locked-secret-input fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 font-mono text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 outline-none placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:placeholder:text-gray-500"
        />
    </div>
</x-dynamic-component>
