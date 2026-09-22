<?php

namespace App\Filament\Concerns;

use App\Filament\Forms\Components\GoogleMapLocation;

trait HasCustomerMapLocation
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function hydrateCustomerMap(array $data): array
    {
        $data['map_location'] = [
            'lat' => (float) ($data['latitude'] ?? GoogleMapLocation::DEFAULT_LAT),
            'lng' => (float) ($data['longitude'] ?? GoogleMapLocation::DEFAULT_LNG),
        ];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function extractCustomerMap(array $data): array
    {
        $map = is_array($data['map_location'] ?? null) ? $data['map_location'] : [];

        $data['latitude'] = $map['lat'] ?? GoogleMapLocation::DEFAULT_LAT;
        $data['longitude'] = $map['lng'] ?? GoogleMapLocation::DEFAULT_LNG;
        unset($data['map_location']);

        return $data;
    }
}
