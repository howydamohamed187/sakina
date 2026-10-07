<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuranAyahResource;
use App\Http\Resources\QuranReciterResource;
use App\Http\Resources\QuranSurahResource;
use App\Models\QuranAyah;
use App\Models\QuranReciter;
use App\Models\QuranSurah;
use App\Services\Quran\QuranService;
use App\Services\Quran\ReciterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class QuranController extends ApiController
{
    public function __construct(
        private readonly QuranService $quran,
        private readonly ReciterService $reciters,
    ) {}

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
        $ayahs = $this->quran->ayahs($surah, $this->searchTerm($request));

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
        $ayahs = $this->quran->ayahs($surah, $this->searchTerm($request));

        return $this->success(
            $this->ayahList($ayahs, $this->reciter($request)),
            __('api.quran.ayahs_ready')
        );
    }

    public function ayah(QuranSurah $surah, int $ayah): JsonResponse
    {
        $model = $this->quran->ayah($surah, $ayah);

        if (! $model) {
            return $this->error(__('api.quran.ayah_not_found'), [], 404);
        }

        return $this->ayahDetails($model, $surah);
    }

    public function ayahByIndex(QuranAyah $ayah): JsonResponse
    {
        return $this->ayahDetails($ayah, QuranSurah::query()->findOrFail($ayah->sura));
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

    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($data['limit'] ?? QuranService::SEARCH_LIMIT);
        $result = $this->quran->search($data['q'], $limit);
        $surahs = $this->quran->surahs()->keyBy('id');

        $ayahs = collect($this->ayahList($result['ayahs'], null, withTafsir: false))
            ->map(fn (array $ayah): array => $ayah + [
                'surah' => [
                    'id' => $ayah['surah_id'],
                    'name' => $surahs[$ayah['surah_id']]->displayName(),
                    'name_ar' => $surahs[$ayah['surah_id']]->name_ar,
                    'name_en' => $surahs[$ayah['surah_id']]->name_en,
                ],
            ])
            ->all();

        return $this->success([
            'query' => $data['q'],
            'surahs' => $this->surahList($result['surahs']),
            'ayahs' => $ayahs,
            'ayahs_total' => $result['total'],
            'limit' => $limit,
        ], __('api.quran.search_ready'));
    }

    private function ayahDetails(QuranAyah $ayah, QuranSurah $surah): JsonResponse
    {
        return $this->success([
            ...$this->ayahList(collect([$ayah]), null)[0],
            'tafsir_source' => $this->quran->tafsir($ayah)?->source,
            'surah' => (new QuranSurahResource($surah))->resolve(),
        ], __('api.quran.ayah_ready'));
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
    private function ayahList(Collection $ayahs, ?QuranReciter $reciter, bool $withTafsir = true): array
    {
        $translations = $this->quran->translations($ayahs, sync: $withTafsir);
        $english = $this->quran->englishTranslations($ayahs, sync: $withTafsir);
        $tafsirs = $withTafsir ? $this->quran->tafsirs($ayahs) : [];

        return $ayahs->map(fn (QuranAyah $ayah): array => (new QuranAyahResource(
            $ayah,
            $reciter,
            $translations[$ayah->verseKey()] ?? null,
            $english[$ayah->verseKey()] ?? null,
            $tafsirs[$ayah->verseKey()] ?? null,
        ))->resolve())->all();
    }

    private function reciter(Request $request): ?QuranReciter
    {
        $id = $request->header('X-Reciter-Id') ?? $request->query('reciter_id');

        return $this->reciters->reciter(is_numeric($id) ? (int) $id : null);
    }

    private function searchTerm(Request $request): ?string
    {
        $search = $request->query('search', $request->query('q'));

        return is_string($search) ? mb_substr($search, 0, 100) : null;
    }
}
