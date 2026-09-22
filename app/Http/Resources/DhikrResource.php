<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Dhikr;
use App\Support\DhikrCategories;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Dhikr */
class DhikrResource extends JsonResource
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
            'category_label' => DhikrCategories::label($this->category),
            'is_favorite' => $this->isFavoritedBy($this->customer) ? 1 : 0,
            'favorites_count' => $this->favoritesCount(),
        ];
    }
}
