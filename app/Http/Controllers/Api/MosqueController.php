<?php

namespace App\Http\Controllers\Api;

use App\Services\Mosques\MosqueProviderException;
use App\Services\Mosques\MosqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class MosqueController extends ApiController
{
    public function __construct(private readonly MosqueService $mosques) {}

    public function nearby(Request $request): JsonResponse
    {
        $data = Validator::make([
            'latitude' => $request->header('X-Latitude') ?? $request->query('latitude'),
            'longitude' => $request->header('X-Longitude') ?? $request->query('longitude'),
            'radius' => $request->query('radius'),
            'limit' => $request->query('limit'),
        ], [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'between:100,50000'],
            'limit' => ['nullable', 'integer', 'between:1,20'],
        ])->validate();

        try {
            $results = $this->mosques->nearby(
                latitude: (float) $data['latitude'],
                longitude: (float) $data['longitude'],
                radius: (int) ($data['radius'] ?? MosqueService::DEFAULT_RADIUS),
                limit: (int) ($data['limit'] ?? MosqueService::DEFAULT_LIMIT),
            );
        } catch (MosqueProviderException $e) {
            Log::warning('Nearby mosques provider error', ['reason' => $e->reason, 'message' => $e->getMessage()]);

            return $this->error(__("api.mosques.errors.{$e->reason}"), [], $e->status(), []);
        }

        $items = array_map(fn (array $row): array => [
            'place_id' => $row['mosque']->placeId,
            'name' => $row['mosque']->name,
            'address' => $row['mosque']->address,
            'latitude' => $row['mosque']->latitude,
            'longitude' => $row['mosque']->longitude,
            'distance_km' => $row['distance_km'],
            'image' => $row['mosque']->photoReference
                ? URL::temporarySignedRoute('api.mosques.photo', now()->addDay(), ['ref' => $row['mosque']->photoReference])
                : null,
        ], $results);

        return $this->success($items, $items ? __('api.mosques.ready') : __('api.mosques.none'));
    }

    public function photo(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $url = $this->mosques->photoUrl((string) $request->query('ref'));
        } catch (MosqueProviderException $e) {
            return $this->error(__("api.mosques.errors.{$e->reason}"), [], $e->status());
        }

        if (! $url) {
            return $this->error(__('api.not_found'), [], 404);
        }

        return redirect()->away($url);
    }
}
