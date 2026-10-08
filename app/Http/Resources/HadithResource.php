<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Hadith;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Hadith */
class HadithResource extends JsonResource
{
    public function __construct(
        $resource,
        private readonly ?Customer $customer = null,
        private readonly bool $detailed = false,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'hadith_text' => $this->body,
            'body' => $this->body,
            'narrator' => $this->narrator,
            'source' => $this->source,
            ...($this->detailed ? ['explanation' => $this->explanation] : []),
            'is_favorite' => $this->isFavoritedBy($this->customer),
            'favorites_count' => $this->favoritesCount(),
        ];
    }
}
