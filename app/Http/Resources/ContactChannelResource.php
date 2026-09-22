<?php

namespace App\Http\Resources;

use App\Models\ContactChannel;
use App\Support\ContactTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContactChannel */
class ContactChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_type_id' => $this->contact_type_id,
            'type' => $this->kind(),
            'type_label' => $this->contactType?->name ?? ContactTypes::label($this->kind()),
            'value' => $this->value,
            'formatted_value' => $this->formattedValue(),
            'status' => $this->status,
            'sort_order' => $this->sort_order,
        ];
    }
}
