<?php

namespace App\Services\Quran;

use App\Models\QuranAyah;
use App\Models\QuranSurah;
use App\Models\QuranTafsir;
use App\Models\QuranTranslation;
use Illuminate\Support\Collection;

/**
 * Textual Quran content only (surahs, ayahs, translation, tafsir, search).
 * Audio lives in ReciterService.
 */
class QuranService
{
    public const SEARCH_LIMIT = 50;

    /** @var array<int, string>|null */
    private ?array $normalizedText = null;

    public function __construct(private readonly QuranEditionSync $editions) {}

    /**
     * @return Collection<int, QuranSurah>
     */
    public function surahs(): Collection
    {
        return QuranSurah::query()->orderBy('id')->get();
    }

    /**
     * @return Collection<int, QuranAyah>
     */
    public function ayahs(QuranSurah $surah, ?string $search = null): Collection
    {
        $ayahs = QuranAyah::query()->where('sura', $surah->id)->orderBy('aya')->get();
        $search = trim((string) $search);

        if ($search === '') {
            return $ayahs;
        }

        $digits = $this->westernDigits($search);

        if (ctype_digit($digits)) {
            return $ayahs->where('aya', (int) $digits)->values();
        }

        $needle = $this->normalize($search);

        if ($needle === '') {
            return $ayahs;
        }

        return $ayahs
            ->filter(fn (QuranAyah $ayah): bool => str_contains($this->normalize($ayah->text), $needle))
            ->values();
    }

    public function ayah(QuranSurah $surah, int $number): ?QuranAyah
    {
        return QuranAyah::query()->where('sura', $surah->id)->where('aya', $number)->first();
    }

    /**
     * @param  Collection<int, QuranAyah>  $ayahs
     * @return array<string, string> translation text keyed by verse key
     */
    public function translations(Collection $ayahs, ?string $locale = null, bool $sync = true): array
    {
        return $this->lookup(QuranTranslation::class, $ayahs, [
            $locale ?? app()->getLocale(),
            config('quran.translation_fallback_locale'),
        ], $sync);
    }

    /**
     * @param  Collection<int, QuranAyah>  $ayahs
     * @return array<string, string> English translation keyed by verse key
     */
    public function englishTranslations(Collection $ayahs, bool $sync = true): array
    {
        return $this->lookup(QuranTranslation::class, $ayahs, ['en'], $sync);
    }

    /**
     * @param  Collection<int, QuranAyah>  $ayahs
     * @return array<string, string> tafsir text keyed by verse key
     */
    public function tafsirs(Collection $ayahs): array
    {
        return $this->lookup(QuranTafsir::class, $ayahs, [
            app()->getLocale(),
            config('quran.tafsir_fallback_locale'),
        ]);
    }

    public function tafsir(QuranAyah $ayah): ?QuranTafsir
    {
        $this->editions->ensure([$ayah->sura]);

        foreach ($this->locales([app()->getLocale(), config('quran.tafsir_fallback_locale')]) as $locale) {
            $tafsir = QuranTafsir::query()
                ->where('sura', $ayah->sura)
                ->where('aya', $ayah->aya)
                ->where('locale', $locale)
                ->first();

            if ($tafsir) {
                return $tafsir;
            }
        }

        return null;
    }

    /**
     * Searches surah names, the Arabic text (diacritics-insensitive), stored translations,
     * verse references ("2:255") and surah numbers.
     *
     * @return array{surahs: Collection<int, QuranSurah>, ayahs: Collection<int, QuranAyah>, total: int}
     */
    public function search(string $query, int $limit = self::SEARCH_LIMIT): array
    {
        $query = trim($this->westernDigits($query));
        $empty = ['surahs' => collect(), 'ayahs' => collect(), 'total' => 0];

        if ($query === '') {
            return $empty;
        }

        if (preg_match('/^(\d{1,3})\s*[:\-\/\s]\s*(\d{1,3})$/', $query, $match)) {
            $ayah = QuranAyah::query()->where('sura', (int) $match[1])->where('aya', (int) $match[2])->first();

            return $ayah ? ['surahs' => collect(), 'ayahs' => collect([$ayah]), 'total' => 1] : $empty;
        }

        if (ctype_digit($query)) {
            $surah = QuranSurah::query()->find((int) $query);

            return $surah ? ['surahs' => collect([$surah]), 'ayahs' => collect(), 'total' => 0] : $empty;
        }

        $needle = $this->normalize($query);
        $latin = mb_strtolower($this->latinKey($query));

        $surahs = $this->surahs()->filter(function (QuranSurah $surah) use ($needle, $latin, $query): bool {
            if ($needle !== '' && str_contains($this->normalize($surah->name_ar), $needle)) {
                return true;
            }

            return $latin !== '' && (
                str_contains(mb_strtolower($this->latinKey((string) $surah->name_en)), $latin)
                || str_contains(mb_strtolower((string) $surah->name_en_translation), mb_strtolower($query))
            );
        })->values();

        $indexes = [];

        if ($needle !== '' && preg_match('/\p{Arabic}/u', $needle)) {
            foreach ($this->normalizedText() as $index => $text) {
                if (str_contains($text, $needle)) {
                    $indexes[] = $index;
                }
            }
        }

        $offsets = $this->surahOffsets();

        QuranTranslation::query()
            ->where('text', 'like', '%'.addcslashes($query, '%_\\').'%')
            ->get(['sura', 'aya'])
            ->each(function (QuranTranslation $row) use (&$indexes, $offsets): void {
                if (isset($offsets[$row->sura])) {
                    $indexes[] = $offsets[$row->sura] + $row->aya;
                }
            });

        $indexes = array_values(array_unique($indexes));
        sort($indexes);

        return [
            'surahs' => $surahs,
            'ayahs' => QuranAyah::query()->whereIn('index', array_slice($indexes, 0, $limit))->orderBy('index')->get(),
            'total' => count($indexes),
        ];
    }

    /**
     * Search-only normalisation; never used for display or storage.
     */
    public function normalize(string $text): string
    {
        $text = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $text);
        $text = strtr($text, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ى' => 'ي', 'ئ' => 'ي', 'ؤ' => 'و', 'ة' => 'ه',
        ]);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Tanzil's global index is sequential, so index = ayahs before the surah + ayah number.
     *
     * @return array<int, int> surah number => count of ayahs in earlier surahs
     */
    private function surahOffsets(): array
    {
        $offsets = [];
        $running = 0;

        foreach ($this->surahs() as $surah) {
            $offsets[$surah->id] = $running;
            $running += $surah->ayahs_count;
        }

        return $offsets;
    }

    /**
     * @return array<int, string> normalized Arabic text keyed by global ayah index
     */
    private function normalizedText(): array
    {
        return $this->normalizedText ??= QuranAyah::query()
            ->orderBy('index')
            ->pluck('text', 'index')
            ->map(fn (string $text): string => $this->normalize($text))
            ->all();
    }

    /**
     * @param  class-string<QuranTranslation|QuranTafsir>  $model
     * @param  Collection<int, QuranAyah>  $ayahs
     * @param  list<string|null>  $locales
     * @return array<string, string>
     */
    private function lookup(string $model, Collection $ayahs, array $locales, bool $sync = true): array
    {
        if ($ayahs->isEmpty()) {
            return [];
        }

        if ($sync) {
            $this->editions->ensure($ayahs->pluck('sura')->unique()->values());
        }

        $rows = $model::query()
            ->whereIn('sura', $ayahs->pluck('sura')->unique())
            ->whereIn('aya', $ayahs->pluck('aya')->unique())
            ->whereIn('locale', $this->locales($locales))
            ->get(['sura', 'aya', 'locale', 'text']);

        $wanted = $ayahs->mapWithKeys(fn (QuranAyah $ayah): array => [$ayah->verseKey() => true]);
        $result = [];

        foreach (array_reverse($this->locales($locales)) as $locale) {
            foreach ($rows->where('locale', $locale) as $row) {
                $key = "{$row->sura}:{$row->aya}";

                if (isset($wanted[$key])) {
                    $result[$key] = $row->text;
                }
            }
        }

        return $result;
    }

    /**
     * @param  list<string|null>  $locales
     * @return list<string>
     */
    private function locales(array $locales): array
    {
        return array_values(array_unique(array_filter($locales)));
    }

    private function latinKey(string $value): string
    {
        $key = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $value));
        $key = (string) preg_replace('/(.)\1+/', '$1', $key);

        return (string) preg_replace('/h$/', '', $key);
    }

    private function westernDigits(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
