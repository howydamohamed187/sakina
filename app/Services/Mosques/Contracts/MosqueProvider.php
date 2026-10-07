<?php

namespace App\Services\Mosques\Contracts;

use App\Services\Mosques\Mosque;
use App\Services\Mosques\MosqueProviderException;

interface MosqueProvider
{
    public function name(): string;

    /**
     * @return list<Mosque>
     *
     * @throws MosqueProviderException
     */
    public function nearby(float $latitude, float $longitude, int $radius, string $locale): array;

    /**
     * Resolves a provider photo reference to a public image URL, without exposing credentials.
     *
     * @throws MosqueProviderException
     */
    public function photoUrl(string $reference): ?string;
}
