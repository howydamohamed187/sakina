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
        private readonly bool $hasTafsir = false,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->index,
            'surah_id' => $this->sura,
            'ayah_number' => $this->aya,
            'verse_key' => $this->verseKey(),
            'text_ar' => $this->text,
            'translation' => $this->translation,
            'audio_url' => $this->reciter?->ayahAudioUrl($this->sura, $this->aya),
            'has_tafsir' => $this->hasTafsir,
        ];
    }
}
