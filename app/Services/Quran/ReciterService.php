<?php

namespace App\Services\Quran;

use App\Models\QuranReciter;
use App\Models\QuranSurah;
use Illuminate\Support\Collection;

/**
 * Audio recitations only. Reciters are managed in the admin panel; each one stores
 * URL templates pointing at the CDN that hosts its recordings.
 */
class ReciterService
{
    /**
     * @return Collection<int, QuranReciter>
     */
    public function reciters(): Collection
    {
        return QuranReciter::query()->active()->ordered()->get();
    }

    /**
     * The requested active reciter, or the default one when $id is missing/unknown.
     */
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
     * @return array{surah_audio: bool, ayah_audio: bool}
     */
    public function capabilities(QuranReciter $reciter): array
    {
        return [
            'surah_audio' => filled($reciter->surah_audio_url_template),
            'ayah_audio' => filled($reciter->ayah_audio_url_template),
        ];
    }

    /**
     * @param  Collection<int, QuranSurah>  $surahs
     * @return list<array{surah_number: int, name: string, audio_url: string}>
     */
    public function surahRecitations(QuranReciter $reciter, Collection $surahs): array
    {
        if (! $this->capabilities($reciter)['surah_audio']) {
            return [];
        }

        return $surahs->map(fn (QuranSurah $surah): array => [
            'surah_number' => $surah->id,
            'name' => $surah->displayName(),
            'audio_url' => (string) $reciter->surahAudioUrl($surah->id),
        ])->all();
    }
}
