<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuranAyahResource;
use App\Http\Resources\QuranReciterResource;
use App\Http\Resources\QuranSurahResource;
use App\Models\QuranAyah;
use App\Models\QuranReciter;
use App\Models\QuranSurah;
use App\Services\Quran\QuranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QuranController extends ApiController
{
    public function __construct(private readonly QuranService $quran) {}

    public function index(Request $request): JsonResponse
    {
        $reciter = $this->reciter($request);

        return $this->success([
            'title' => __('api.quran.title'),
            'introduction' => [
                'title' => __('api.quran.introduction.title'),
                'text' => __('api.quran.introduction.text'),
            ],
            'labels' => [
                'reciter' => __('api.quran.labels.reciter'),
                'index' => __('api.quran.labels.index'),
                'search_placeholder' => __('api.quran.labels.search_placeholder'),
            ],
            'reciter' => $reciter ? (new QuranReciterResource($reciter))->resolve() : null,
            'surahs' => $this->surahList($this->quran->surahs()),
            'source' => config('quran.source'),
        ], __('api.quran.ready'));
    }

    public function surahs(): JsonResponse
    {
        return $this->success($this->surahList($this->quran->surahs()), __('api.quran.surahs_ready'));
    }

    public function surah(Request $request, QuranSurah $surah): JsonResponse
    {
        $reciter = $this->reciter($request);
        $ayahs = $this->quran->ayahs($surah, $this->search($request));

        return $this->success([
            ...(new QuranSurahResource($surah))->resolve(),
            'information' => [
                'title' => __('api.quran.labels.information'),
                'name' => $surah->displayName(),
                'revelation_type' => [
                    'key' => $surah->revelation_type,
                    'name' => $surah->revelationTypeLabel(),
                ],
                'ayahs_count' => $surah->ayahs_count,
                'description' => $surah->informationText(),
            ],
            'surah_audio' => [
                'reciter_id' => $reciter?->id,
                'audio_url' => $reciter?->surahAudioUrl($surah->id),
            ],
            'reciter' => $reciter ? (new QuranReciterResource($reciter))->resolve() : null,
            'ayahs' => $this->ayahList($ayahs, $reciter),
        ], __('api.quran.surah_ready'));
    }

    public function ayahs(Request $request, QuranSurah $surah): JsonResponse
    {
        $ayahs = $this->quran->ayahs($surah, $this->search($request));

        return $this->success(
            $this->ayahList($ayahs, $this->reciter($request)),
            __('api.quran.ayahs_ready')
        );
    }

    public function tafsir(QuranAyah $ayah): JsonResponse
    {
        $tafsir = $this->quran->tafsir($ayah);

        if (! $tafsir) {
            return $this->error(__('api.quran.tafsir_unavailable'), [], 404);
        }

        return $this->success([
            'ayah_id' => $ayah->index,
            'verse_key' => $ayah->verseKey(),
            'text_ar' => $ayah->text,
            'text' => $tafsir->text,
            'locale' => $tafsir->locale,
            'source' => $tafsir->source,
        ], __('api.quran.tafsir_ready'));
    }

    public function reciters(): JsonResponse
    {
        return $this->success(
            QuranReciterResource::collection($this->quran->reciters())->resolve(),
            __('api.quran.reciters_ready')
        );
    }

    public function audio(QuranReciter $reciter, QuranSurah $surah): JsonResponse
    {
        if ($reciter->status !== 'active') {
            return $this->error(__('api.not_found'), [], 404);
        }

        $ayahs = $this->quran->ayahs($surah);

        return $this->success([
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'audio_url' => $reciter->surahAudioUrl($surah->id),
            'ayahs' => $ayahs->map(fn (QuranAyah $ayah): array => [
                'id' => $ayah->index,
                'verse_key' => $ayah->verseKey(),
                'audio_url' => $reciter->ayahAudioUrl($ayah->sura, $ayah->aya),
            ])->all(),
        ], __('api.quran.audio_ready'));
    }

    /**
     * @param  Collection<int, QuranSurah>  $surahs
     */
    private function surahList(Collection $surahs): array
    {
        return QuranSurahResource::collection($surahs)->resolve();
    }

    /**
     * @param  Collection<int, QuranAyah>  $ayahs
     */
    private function ayahList(Collection $ayahs, ?QuranReciter $reciter): array
    {
        $translations = $this->quran->translations($ayahs);
        $tafsirs = $this->quran->tafsirKeys($ayahs);

        return $ayahs->map(fn (QuranAyah $ayah): array => (new QuranAyahResource(
            $ayah,
            $reciter,
            $translations[$ayah->verseKey()] ?? null,
            isset($tafsirs[$ayah->verseKey()]),
        ))->resolve())->all();
    }

    private function reciter(Request $request): ?QuranReciter
    {
        $id = $request->header('X-Reciter-Id') ?? $request->query('reciter_id');

        return $this->quran->reciter(is_numeric($id) ? (int) $id : null);
    }

    private function search(Request $request): ?string
    {
        $search = $request->query('search', $request->query('q'));

        return is_string($search) ? mb_substr($search, 0, 100) : null;
    }
}
