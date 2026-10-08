<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RuqyahStepResource;
use App\Models\Customer;
use App\Models\RuqyahStep;
use App\Settings\RuqyahSettings;
use App\Support\StoredSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only: the repetition counter lives in the mobile app, so there is no
 * increment/progress endpoint here.
 */
class RuqyahController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user() instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $steps = RuqyahStep::query()
            ->active()
            ->ordered()
            ->get(['id', 'title', 'instruction', 'content', 'repeat_count']);

        $settings = StoredSettings::ruqyah();
        $defaults = RuqyahSettings::defaults();

        return $this->success([
            'title' => $settings?->title ?: $defaults['title'],
            'description' => $settings?->description ?? $defaults['description'],
            'total_steps' => $steps->count(),
            'steps' => $steps->values()
                ->map(fn (RuqyahStep $step, int $index): array => (new RuqyahStepResource($step, $index + 1))->resolve())
                ->all(),
        ], __('api.ruqyah_ready'));
    }
}
