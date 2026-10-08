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
            'text' => $this->body,
            'body' => $this->body,
            'category' => [
                'id' => $this->category,
                'name' => DhikrCategories::label($this->category),
            ],
            'category_label' => DhikrCategories::label($this->category),
            'repeat_count' => $this->target_count ?? 1,
            'is_countable' => (bool) $this->is_countable,
            'target_count' => $this->target_count,
            ...($this->detailed ? ['description' => $this->description] : []),
            'is_favorite' => $this->isFavoritedBy($this->customer),
            'favorites_count' => $this->favoritesCount(),
        ];
    }
}
