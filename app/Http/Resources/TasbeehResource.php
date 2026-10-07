<?php

namespace App\Http\Resources;

use App\Models\Dhikr;
use App\Services\TasbeehProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Expects `progress` to be eager-loaded and scoped to the current customer.
 *
 * @mixin Dhikr
 */
class TasbeehResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'text' => $this->body,
            'description' => $this->description,
            'target_count' => $this->target_count,
            'progress' => TasbeehProgressService::present(
                $this->relationLoaded('progress') ? $this->progress->first() : null
            ),
        ];
    }
}
