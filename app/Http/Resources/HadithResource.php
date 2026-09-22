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
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'is_favorite' => $this->isFavoritedBy($this->customer) ? 1 : 0,
            'favorites_count' => $this->favoritesCount(),
        ];
    }
}
