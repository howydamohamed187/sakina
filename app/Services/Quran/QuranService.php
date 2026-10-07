<?php

namespace App\Services\Quran;

use App\Models\QuranAyah;
use App\Models\QuranReciter;
use App\Models\QuranSurah;
use App\Models\QuranTafsir;
use App\Models\QuranTranslation;
use Illuminate\Support\Collection;

class QuranService
{
    /**
     * @return Collection<int, QuranSurah>
     */
    public function surahs(): Collection
    {
        return QuranSurah::query()->orderBy('id')->get();
    }

    /**
     * @return Collection<int, QuranReciter>
     */
    public function reciters(): Collection
    {
        return QuranReciter::query()->active()->ordered()->get();
    }

    public function reciter(?int $id = null): ?QuranReciter
    {
        if ($id) {
            $reciter = QuranReciter::query()->active()->find($id);

            if ($reciter) {
                return $reciter;
            }
        }

        return QuranReciter::query()->active()->ordered()->first();
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

    /**
     * @param  Collection<int, QuranAyah>  $ayahs
     * @return array<string, string> translation text keyed by verse key
     */
    public function translations(Collection $ayahs, ?string $locale = null): array
    {
        return $this->lookup(QuranTranslation::class, $ayahs, [
            $locale ?? app()->getLocale(),
            config('quran.translation_fallback_locale'),
        ]);
    }

    /**
     * @param  Collection<int, QuranAyah>  $ayahs
     * @return array<string, true> verse keys that have a tafsir
     */
    public function tafsirKeys(Collection $ayahs): array
    {
        return array_fill_keys(array_keys($this->lookup(QuranTafsir::class, $ayahs, [
            app()->getLocale(),
            config('quran.tafsir_fallback_locale'),
        ])), true);
    }

    public function tafsir(QuranAyah $ayah): ?QuranTafsir
    {
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
     * @param  class-string<QuranTranslation|QuranTafsir>  $model
     * @param  Collection<int, QuranAyah>  $ayahs
     * @param  list<string|null>  $locales
     * @return array<string, string>
     */
    private function lookup(string $model, Collection $ayahs, array $locales): array
    {
        if ($ayahs->isEmpty()) {
            return [];
        }

        $rows = $model::query()
            ->whereIn('sura', $ayahs->pluck('sura')->unique())
            ->whereIn('aya', $ayahs->pluck('aya')->unique())
            ->whereIn('locale', $this->locales($locales))
            ->get(['sura', 'aya', 'locale', 'text']);

        $result = [];

        foreach (array_reverse($this->locales($locales)) as $locale) {
            foreach ($rows->where('locale', $locale) as $row) {
                $result["{$row->sura}:{$row->aya}"] = $row->text;
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

    private function westernDigits(string $value): string
    {
        return strtr($value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
