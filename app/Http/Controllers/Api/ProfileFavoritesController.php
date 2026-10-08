<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DhikrResource;
use App\Http\Resources\DuaResource;
use App\Http\Resources\HadithResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The profile "Favorites" section: everything the customer saved, grouped by type,
 * most recently saved first.
 */
class ProfileFavoritesController extends ApiController
{
    public const TYPES = ['hadith', 'dhikr', 'dua'];

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $type = $request->validate([
            'type' => ['nullable', 'string', Rule::in(self::TYPES)],
        ])['type'] ?? null;

        $data = [];

        if ($type === null || $type === 'hadith') {
            $data['hadiths'] = $this->hadiths($customer);
        }

        if ($type === null || $type === 'dhikr') {
            $data['adhkar'] = $this->adhkar($customer);
        }

        if ($type === null || $type === 'dua') {
            $data['duas'] = $this->duas($customer);
        }

        $counts = [
            'favorite_hadiths_count' => $customer->favoriteHadiths()->active()->count(),
            'favorite_adhkar_count' => $customer->favoriteDhikrs()->active()->count(),
            'favorite_duas_count' => $customer->favoriteDuas()->active()->count(),
        ];

        return $this->success([
            ...$data,
            'counts' => [...$counts, 'total' => array_sum($counts)],
        ], __('api.favorites_ready'));
    }

    private function hadiths(Customer $customer): array
    {
        return $customer->favoriteHadiths()
            ->active()
            ->withFavoriteFor($customer)
            ->withCount('favorites')
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(fn ($hadith): array => (new HadithResource($hadith, $customer))->resolve())
            ->all();
    }

    private function adhkar(Customer $customer): array
    {
        return $customer->favoriteDhikrs()
            ->active()
            ->with(['favorites' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->withCount('favorites')
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(fn ($dhikr): array => (new DhikrResource($dhikr, $customer))->resolve())
            ->all();
    }

    private function duas(Customer $customer): array
    {
        return $customer->favoriteDuas()
            ->active()
            ->with(['favorites' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->withCount('favorites')
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(fn ($dua): array => (new DuaResource($dua, $customer))->resolve())
            ->all();
    }
}
