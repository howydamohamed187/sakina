<?php

namespace App\Filament\Forms\Components;

use App\Support\StoredSettings;
use Filament\Forms\Components\Field;

class GoogleMapLocation extends Field
{
    protected string $view = 'filament.forms.components.google-map-location';

    public const DEFAULT_LAT = 24.7136;

    public const DEFAULT_LNG = 46.6753;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'lat' => self::DEFAULT_LAT,
            'lng' => self::DEFAULT_LNG,
        ]);

        $this->afterStateHydrated(function (GoogleMapLocation $component, mixed $state): void {
            $component->state($component->normalizeState($state));
        });

        $this->dehydrateStateUsing(fn (mixed $state): array => $this->normalizeState($state));

        $this->columnSpanFull();
    }

    public function getApiKey(): ?string
    {
        return StoredSettings::googleMapKey();
    }

    public function getLatitude(): float
    {
        return (float) ($this->normalizeState($this->getState())['lat']);
    }

    public function getLongitude(): float
    {
        return (float) ($this->normalizeState($this->getState())['lng']);
    }

    public function getAddressStatePath(): string
    {
        return (string) preg_replace('/[^.]+$/', 'app_address', $this->getStatePath());
    }

    /**
     * @return array{lat: float, lng: float}
     */
    public function normalizeState(mixed $state): array
    {
        if (is_array($state) && isset($state['lat'], $state['lng'])) {
            return [
                'lat' => (float) $state['lat'],
                'lng' => (float) $state['lng'],
            ];
        }

        return [
            'lat' => self::DEFAULT_LAT,
            'lng' => self::DEFAULT_LNG,
        ];
    }
}
