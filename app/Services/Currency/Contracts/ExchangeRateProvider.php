<?php

namespace App\Services\Currency\Contracts;

use App\Services\Providers\ProviderException;

interface ExchangeRateProvider
{
    public function name(): string;

    /**
     * Units of each currency for one unit of $base.
     *
     * @return array{rates: array<string, float>, updated_at: string|null}
     *
     * @throws ProviderException
     */
    public function rates(string $base): array;
}
