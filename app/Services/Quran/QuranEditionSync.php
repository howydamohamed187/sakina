<?php

namespace App\Services\Quran;

use App\Models\QuranSurah;
use App\Models\QuranTafsir;
use App\Models\QuranTranslation;
use App\Services\Providers\ProviderException;
use App\Services\Quran\Contracts\QuranEditionProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Translations and tafsir are fetched from the provider once per surah and stored
 * in quran_translations / quran_tafsirs, so reads never depend on the provider.
 */
class QuranEditionSync
{
    private const RETRY_AFTER = 600;

    public function __construct(private readonly QuranEditionProvider $provider) {}

    /**
     * @return array<string, class-string<QuranTranslation|QuranTafsir>> type => model
     */
    public static function types(): array
    {
        return ['translations' => QuranTranslation::class, 'tafsirs' => QuranTafsir::class];
    }

    public static function supports(string $type, string $locale): bool
    {
        return filled(config("quran.editions.{$type}.{$locale}"));
    }

    /**
     * Best effort: a provider failure leaves the surah without that edition and is retried later.
     *
     * @param  iterable<int>  $surahs
     */
    public function ensure(iterable $surahs): void
    {
        foreach ($surahs as $surah) {
            foreach (self::types() as $type => $model) {
                foreach ((array) config("quran.editions.{$type}") as $locale => $edition) {
                    try {
                        $this->sync((int) $surah, $model, (string) $locale, (string) $edition);
                    } catch (ProviderException $e) {
                        Log::warning('Quran edition sync failed', [
                            'surah' => $surah,
                            'edition' => $edition,
                            'reason' => $e->reason,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param  class-string<QuranTranslation|QuranTafsir>  $model
     *
     * @throws ProviderException
     */
    public function sync(int $surah, string $model, string $locale, string $edition, bool $force = false, bool $respectBackoff = true): bool
    {
        $expected = (int) QuranSurah::query()->whereKey($surah)->value('ayahs_count');
        $source = $this->provider->name().':'.$edition;
        $failedKey = "quran_edition_failed:{$edition}:{$surah}";

        if ($expected === 0) {
            return false;
        }

        if (! $force && $this->stored($model, $surah, $locale, $source) === $expected) {
            return false;
        }

        if ($respectBackoff && Cache::has($failedKey)) {
            return false;
        }

        try {
            $texts = $this->provider->surah($surah, $edition);
        } catch (ProviderException $e) {
            Cache::put($failedKey, true, self::RETRY_AFTER);

            throw $e;
        }

        ksort($texts);

        if (count($texts) !== $expected || array_keys($texts) !== range(1, $expected)) {
            Cache::put($failedKey, true, self::RETRY_AFTER);

            throw new ProviderException(
                ProviderException::INVALID_RESPONSE,
                "{$edition} returned ".count($texts)." ayahs for surah {$surah}, expected {$expected}."
            );
        }

        $now = now();
        $rows = [];

        foreach ($texts as $aya => $text) {
            $rows[] = [
                'sura' => $surah,
                'aya' => $aya,
                'locale' => $locale,
                'text' => $text,
                'source' => $source,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($model, $rows): void {
            foreach (array_chunk($rows, 100) as $chunk) {
                $model::query()->upsert($chunk, ['sura', 'aya', 'locale'], ['text', 'source', 'updated_at']);
            }
        });

        return true;
    }

    /**
     * @param  class-string<QuranTranslation|QuranTafsir>  $model
     */
    private function stored(string $model, int $surah, string $locale, string $source): int
    {
        return $model::query()
            ->where('sura', $surah)
            ->where('locale', $locale)
            ->where('source', $source)
            ->count();
    }
}
