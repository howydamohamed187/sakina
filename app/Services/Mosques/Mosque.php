<?php

namespace App\Services\Mosques;

final readonly class Mosque
{
    public function __construct(
        public string $placeId,
        public string $name,
        public ?string $address,
        public float $latitude,
        public float $longitude,
        public ?string $photoReference = null,
    ) {}

    /**
     * @return array{place_id: string, name: string, address: ?string, latitude: float, longitude: float, photo_reference: ?string}
     */
    public function toArray(): array
    {
        return [
            'place_id' => $this->placeId,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'photo_reference' => $this->photoReference,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            placeId: (string) $data['place_id'],
            name: (string) $data['name'],
            address: $data['address'] ?? null,
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            photoReference: $data['photo_reference'] ?? null,
        );
    }
}
