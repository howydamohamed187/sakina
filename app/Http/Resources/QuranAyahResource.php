<?php

namespace App\Http\Resources;

use App\Models\QuranAyah;
use App\Models\QuranReciter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuranAyah */
class QuranAyahResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly ?QuranReciter $reciter = null,
        private readonly ?string $translation = null,
        private readonly ?string $translationEn = null,
        private readonly ?string $tafsir = null,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->index,
            'surah_id' => $this->sura,
            'number' => $this->aya,
            'ayah_number' => $this->aya,
            'verse_key' => $this->verseKey(),
            'text_ar' => $this->text,
            'translation' => $this->translation,
            'translation_en' => $this->translationEn,
            'tafsir' => $this->tafsir,
            'has_tafsir' => $this->tafsir !== null,
            'audio_url' => $this->when($this->reciter !== null, fn () => $this->reciter->ayahAudioUrl($this->sura, $this->aya)),
        ];
    }
}
