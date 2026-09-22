<?php

namespace App\Filament\Forms\Components;

use App\Support\PhoneNumber;
use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PhoneField extends Field
{
    protected string $view = 'filament.forms.components.phone-field';

    protected ?string $uniqueTable = 'users';

    protected string $uniqueColumn = 'phone';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'country' => PhoneNumber::DEFAULT_COUNTRY,
            'national' => '',
        ]);

        $this->afterStateHydrated(function (PhoneField $component, $state): void {
            if (is_array($state) && array_key_exists('country', $state)) {
                return;
            }

            $stored = is_string($state) ? $state : $component->getRecord()?->getAttribute($component->getName());
            $country = PhoneNumber::countryFromStored($stored);

            $component->state([
                'country' => $country,
                'national' => PhoneNumber::nationalFromStored($stored, $country),
            ]);
        });

        $this->dehydrateStateUsing(fn (mixed $state): ?string => $this->toE164($state));

        $this->rule(function (PhoneField $component): Closure {
            return function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                $e164 = $component->toE164($value);

                if (blank($e164)) {
                    if ($component->isRequired()) {
                        $fail(__('validation.required'));
                    }

                    return;
                }

                $country = is_array($value)
                    ? ($value['country'] ?? PhoneNumber::DEFAULT_COUNTRY)
                    : PhoneNumber::countryFromStored($e164);

                if (! PhoneNumber::isValid($country, is_array($value) ? ($value['national'] ?? null) : $e164)) {
                    $fail(__('app.phone.invalid'));

                    return;
                }

                if (! $component->getUniqueTable()) {
                    return;
                }

                $query = DB::table($component->getUniqueTable())
                    ->where($component->getUniqueColumn(), $e164);

                $record = $component->getRecord();

                if ($record instanceof Model && $record->getKey()) {
                    $query->where($record->getKeyName(), '!=', $record->getKey());
                }

                if ($query->exists()) {
                    $fail(__('validation.unique', [
                        'attribute' => __('validation.attributes.phone'),
                    ]));
                }
            };
        });
    }

    public function uniqueOn(string $table, string $column = 'phone'): static
    {
        $this->uniqueTable = $table;
        $this->uniqueColumn = $column;

        return $this;
    }

    public function withoutUnique(): static
    {
        $this->uniqueTable = null;

        return $this;
    }

    public function getUniqueTable(): ?string
    {
        return $this->uniqueTable;
    }

    public function getUniqueColumn(): string
    {
        return $this->uniqueColumn;
    }

    /**
     * @return array<string, string>
     */
    public function getCountryOptions(): array
    {
        return PhoneNumber::compactOptions();
    }

    public function getPlaceholder(): ?string
    {
        $state = $this->getState();
        $country = is_array($state) ? ($state['country'] ?? PhoneNumber::DEFAULT_COUNTRY) : PhoneNumber::DEFAULT_COUNTRY;

        return $country === 'EG' ? '10xxxxxxxx' : null;
    }

    public function toE164(mixed $state): ?string
    {
        if (is_string($state)) {
            return $state !== '' ? $state : null;
        }

        if (! is_array($state)) {
            return null;
        }

        return PhoneNumber::toE164($state['country'] ?? null, $state['national'] ?? null);
    }
}
