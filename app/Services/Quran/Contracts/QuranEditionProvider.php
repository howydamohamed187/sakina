<?php

namespace App\Services\Quran\Contracts;

use App\Services\Providers\ProviderException;

/**
 * Supplies translations and tafsir aligned to the Quran's ayahs. The Arabic
 * Quran text itself never comes from a provider.
 */
interface QuranEditionProvider
{
    public function name(): string;

    /**
     * @return array<int, string> edition text keyed by ayah number within the surah
     *
     * @throws ProviderException
     */
    public function surah(int $surah, string $edition): array;
}
