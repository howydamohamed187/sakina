<?php

namespace App\Support;

use App\Http\Resources\DhikrResource;
use App\Http\Resources\DuaResource;
use App\Http\Resources\HadithResource;
use App\Models\Customer;

/**
 * The customer's saved items per type (active only, most recently saved first),
 * shaped exactly like the items of the corresponding list endpoint.
 */
class CustomerFavorites
{
    public static function hadiths(Customer $customer): array
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

    public static function adhkar(Customer $customer): array
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

    public static function duas(Customer $customer): array
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
