<?php

namespace App\Filament\Forms;

use App\Support\Locales;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;

class TranslatableFields
{
    /**
     * @param  array<int, string>|null  $locales
     */
    public static function apply(Section $section, ?array $locales = null): Section
    {
        $locales ??= ['ar', 'en'];
        $components = $section->getChildComponents();

        $tabs = [];

        foreach ($locales as $locale) {
            $tabs[] = Tab::make($locale)
                ->label(Locales::label($locale))
                ->schema(
                    collect($components)
                        ->map(fn (Component $component): Component => static::localize($component, $locale))
                        ->all()
                );
        }

        return $section->schema([
            Tabs::make('translations')
                ->tabs($tabs)
                ->contained(false)
                ->columnSpanFull(),
        ]);
    }

    protected static function localize(Component $component, string $locale): Component
    {
        if ($component instanceof TextInput) {
            return TextInput::make($component->getName().'.'.$locale)
                ->label($component->getLabel())
                ->required($component->isRequired())
                ->maxLength(255)
                ->columnSpanFull()
                ->formatStateUsing(fn (mixed $state): string => static::scalarState($state, $locale))
                ->dehydrateStateUsing(fn (mixed $state): string => static::scalarState($state, $locale));
        }

        if ($component instanceof Field && method_exists($component, 'name')) {
            return $component->getClone()->name($component->getName().'.'.$locale);
        }

        return $component->getClone();
    }

    protected static function scalarState(mixed $state, string $locale): string
    {
        if (is_array($state)) {
            $value = $state[$locale] ?? '';

            return is_scalar($value) ? (string) $value : '';
        }

        if (is_object($state)) {
            $value = $state->{$locale} ?? '';

            return is_scalar($value) ? (string) $value : '';
        }

        return is_scalar($state) ? (string) $state : '';
    }
}
