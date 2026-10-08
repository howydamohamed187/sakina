<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Support\CustomerFavorites;
use App\Support\FavoriteTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The profile "Favorites" section: everything the customer saved, grouped by type,
 * most recently saved first.
 */
class ProfileFavoritesController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $type = FavoriteTypes::normalize($request->validate([
            'type' => ['nullable', 'string', Rule::in(FavoriteTypes::accepted())],
        ])['type'] ?? null);

        $data = [];

        if ($type === null || $type === FavoriteTypes::HADITH) {
            $data['hadiths'] = CustomerFavorites::hadiths($customer);
        }

        if ($type === null || $type === FavoriteTypes::DHIKR) {
            $data['adhkar'] = CustomerFavorites::adhkar($customer);
        }

        if ($type === null || $type === FavoriteTypes::DUA) {
            $data['duas'] = CustomerFavorites::duas($customer);
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
}
