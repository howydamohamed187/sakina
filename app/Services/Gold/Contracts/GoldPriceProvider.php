<?php

namespace App\Services\Gold\Contracts;

use App\Services\Providers\ProviderException;

interface GoldPriceProvider
{
    public function name(): string;

    /**
     * Current 24K spot price for one troy ounce.
     *
     * @return array{price: float, currency: string, updated_at: string|null}
     *
     * @throws ProviderException
     */
    public function ouncePrice(): array;
}
