<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\HadithResource;
use App\Models\Customer;
use App\Models\Hadith;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HadithController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $search = trim((string) $request->query('q', $request->query('search', '')));

        $hadiths = Hadith::query()
            ->active()
            ->with(['favorites' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->withCount('favorites')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $favorites = $hadiths
            ->filter(fn (Hadith $hadith): bool => $hadith->isFavoritedBy($customer))
            ->values();

        return $this->success([
            'favorites' => $favorites
                ->map(fn (Hadith $hadith): array => (new HadithResource($hadith, $customer))->resolve())
                ->all(),
            'hadiths' => $hadiths
                ->map(fn (Hadith $hadith): array => (new HadithResource($hadith, $customer))->resolve())
                ->all(),
        ], __('api.hadiths_ready'));
    }

    public function show(Request $request, Hadith $hadith): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if (! $hadith->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $hadith->loadCount('favorites');

        return $this->success(
            (new HadithResource($hadith, $customer))->resolve(),
            __('api.success')
        );
    }

    public function favorite(Request $request, Hadith $hadith): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if (! $hadith->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $customer->favoriteHadiths()->syncWithoutDetaching([$hadith->id]);
        $hadith->loadCount('favorites');

        return $this->success(
            (new HadithResource($hadith->fresh(), $customer))->resolve(),
            __('api.hadith_favorited')
        );
    }

    public function unfavorite(Request $request, Hadith $hadith): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $customer->favoriteHadiths()->detach($hadith->id);
        $hadith->loadCount('favorites');

        return $this->success(
            (new HadithResource($hadith->fresh(), $customer))->resolve(),
            __('api.hadith_unfavorited')
        );
    }

    private function customer(Request $request): Customer|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        return $user;
    }
}
