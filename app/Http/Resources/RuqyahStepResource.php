<?php

namespace App\Http\Resources;

use App\Models\RuqyahStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RuqyahStep */
class RuqyahStepResource extends JsonResource
{
    public function __construct($resource, private readonly int $stepNumber)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'step_number' => $this->stepNumber,
            'title' => self::stepTitle($this->stepNumber),
            'name' => $this->title,
            'instruction' => $this->instruction,
            'content' => $this->content,
            'repeat_count' => max(1, (int) $this->repeat_count),
        ];
    }

    public static function stepTitle(int $number): string
    {
        $key = 'api.ruqyah_step_ordinals.'.$number;

        return trans()->hasForLocale($key) ? __($key) : __('api.ruqyah_step_number', ['number' => $number]);
    }
}
