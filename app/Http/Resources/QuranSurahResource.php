<?php

namespace App\Http\Resources;

use App\Models\QuranSurah;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuranSurah */
class QuranSurahResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->id,
            'name' => $this->displayName(),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name_en_translation' => $this->name_en_translation,
            'ayahs_count' => $this->ayahs_count,
            'ayahs_count_label' => trans_choice('api.quran.ayahs_count', $this->ayahs_count, ['count' => $this->ayahs_count]),
            'revelation_type' => [
                'key' => $this->revelation_type,
                'name' => $this->revelationTypeLabel(),
            ],
        ];
    }
}
