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
        private readonly bool $detailed = false,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'title' => $this->title,
            'dua_text' => $this->body,
            'body' => $this->body,
            'category' => [
                'id' => $this->category,
                'name' => DuaCategories::label($this->category),
            ],
            'category_label' => DuaCategories::label($this->category),
            ...($this->detailed ? ['description' => $this->description] : []),
            'is_favorite' => $this->isFavoritedBy($this->customer),
            'favorites_count' => $this->favoritesCount(),
        ];
    }
}
