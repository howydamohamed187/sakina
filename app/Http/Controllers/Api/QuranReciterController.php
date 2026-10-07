<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuranReciterResource;
use App\Models\QuranAyah;
use App\Models\QuranReciter;
use App\Models\QuranSurah;
use App\Services\Quran\QuranService;
use App\Services\Quran\ReciterService;
use Illuminate\Http\JsonResponse;

class QuranReciterController extends ApiController
{
    public function __construct(
        private readonly ReciterService $reciters,
        private readonly QuranService $quran,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success(
            QuranReciterResource::collection($this->reciters->reciters())->resolve(),
            __('api.quran.reciters_ready')
        );
    }

    public function show(QuranReciter $reciter): JsonResponse
    {
        if ($reciter->status !== 'active') {
            return $this->error(__('api.quran.reciter_not_found'), [], 404);
        }

        return $this->success([
            ...(new QuranReciterResource($reciter))->resolve(),
            'capabilities' => $this->reciters->capabilities($reciter),
            'surahs' => $this->reciters->surahRecitations($reciter, $this->quran->surahs()),
        ], __('api.quran.reciter_ready'));
    }

    public function surahAudio(QuranReciter $reciter, QuranSurah $surah): JsonResponse
    {
        if ($reciter->status !== 'active') {
            return $this->error(__('api.quran.reciter_not_found'), [], 404);
        }

        if (! $this->reciters->capabilities($reciter)['surah_audio']) {
            return $this->error(__('api.quran.surah_audio_unsupported'), [], 422);
        }

        return $this->success([
            'reciter_id' => $reciter->id,
            'surah_number' => $surah->id,
            'audio_url' => $reciter->surahAudioUrl($surah->id),
        ], __('api.quran.audio_ready'));
    }

    public function ayahAudio(QuranReciter $reciter, QuranSurah $surah, int $ayah): JsonResponse
    {
        if ($reciter->status !== 'active') {
            return $this->error(__('api.quran.reciter_not_found'), [], 404);
        }

        if ($ayah < 1 || $ayah > $surah->ayahs_count) {
            return $this->error(__('api.quran.ayah_not_found'), [], 404);
        }

        if (! $this->reciters->capabilities($reciter)['ayah_audio']) {
            return $this->error(__('api.quran.ayah_audio_unsupported'), [], 422);
        }

        return $this->success([
            'reciter_id' => $reciter->id,
            'surah_number' => $surah->id,
            'ayah_number' => $ayah,
            'verse_key' => "{$surah->id}:{$ayah}",
            'audio_url' => $reciter->ayahAudioUrl($surah->id, $ayah),
        ], __('api.quran.audio_ready'));
    }

    /**
     * Full surah audio plus every ayah's audio in one call (kept for existing clients).
     */
    public function audio(QuranReciter $reciter, QuranSurah $surah): JsonResponse
    {
        if ($reciter->status !== 'active') {
            return $this->error(__('api.quran.reciter_not_found'), [], 404);
        }

        return $this->success([
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'audio_url' => $reciter->surahAudioUrl($surah->id),
            'ayahs' => $this->quran->ayahs($surah)->map(fn (QuranAyah $ayah): array => [
                'id' => $ayah->index,
                'verse_key' => $ayah->verseKey(),
                'audio_url' => $reciter->ayahAudioUrl($ayah->sura, $ayah->aya),
            ])->all(),
        ], __('api.quran.audio_ready'));
    }
}
