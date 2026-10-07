<?php

namespace App\Http\Resources;

use App\Models\QuranReciter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuranReciter */
class QuranReciterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->displayName(),
            'image' => $this->imageUrl(),
            'is_default' => $this->is_default,
        ];
    }
}
