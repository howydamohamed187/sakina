<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DhikrResource;
use App\Models\Customer;
use App\Models\Dhikr;
use App\Support\DhikrCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DhikrController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $search = trim((string) $request->query('q', $request->query('search', '')));
        $category = $request->query('category');

        $dhikrs = Dhikr::query()
            ->active()
            ->with(['favorites' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->withCount('favorites')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%');
                });
            })
            ->when(filled($category), fn ($query) => $query->where('category', $category))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $favorites = $dhikrs
            ->filter(fn (Dhikr $dhikr): bool => $dhikr->isFavoritedBy($customer))
            ->values();

        return $this->success([
            'favorites' => $favorites
                ->map(fn (Dhikr $dhikr): array => (new DhikrResource($dhikr, $customer))->resolve())
                ->all(),
            'adhkar' => $dhikrs
                ->map(fn (Dhikr $dhikr): array => (new DhikrResource($dhikr, $customer))->resolve())
                ->all(),
            'categories' => collect(DhikrCategories::options())
                ->map(fn (string $label, string $value): array => [
                    'value' => $value,
                    'label' => $label,
                ])
                ->values()
                ->all(),
        ], __('api.adhkar_ready'));
    }

    public function show(Request $request, Dhikr $dhikr): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if (! $dhikr->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $dhikr->loadCount('favorites');

        return $this->success(
            (new DhikrResource($dhikr, $customer))->resolve(),
            __('api.success')
        );
    }

    public function favorite(Request $request, Dhikr $dhikr): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if (! $dhikr->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $customer->favoriteDhikrs()->syncWithoutDetaching([$dhikr->id]);

        return $this->success(
            (new DhikrResource($dhikr->fresh(), $customer))->resolve(),
            __('api.dhikr_favorited')
        );
    }

    public function unfavorite(Request $request, Dhikr $dhikr): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $customer->favoriteDhikrs()->detach($dhikr->id);

        return $this->success(
            (new DhikrResource($dhikr->fresh(), $customer))->resolve(),
            __('api.dhikr_unfavorited')
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
