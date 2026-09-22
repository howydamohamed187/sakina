<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Dua;
use App\Support\DuaCategories;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Dua */
class DuaResource extends JsonResource
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
            'category' => $this->category,
            'category_label' => DuaCategories::label($this->category),
            'is_favorite' => $this->isFavoritedBy($this->customer) ? 1 : 0,
            'favorites_count' => $this->favoritesCount(),
        ];
    }
}
