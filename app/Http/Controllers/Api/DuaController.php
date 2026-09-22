<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DuaResource;
use App\Models\Customer;
use App\Models\Dua;
use App\Support\DuaCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DuaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $search = trim((string) $request->query('q', $request->query('search', '')));
        $category = $request->query('category');

        $duas = Dua::query()
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

        $favorites = $duas
            ->filter(fn (Dua $dua): bool => $dua->isFavoritedBy($customer))
            ->values();

        return $this->success([
            'favorites' => $favorites
                ->map(fn (Dua $dua): array => (new DuaResource($dua, $customer))->resolve())
                ->all(),
            'duas' => $duas
                ->map(fn (Dua $dua): array => (new DuaResource($dua, $customer))->resolve())
                ->all(),
            'categories' => collect(DuaCategories::options())
                ->map(fn (string $label, string $value): array => [
                    'value' => $value,
                    'label' => $label,
                ])
                ->values()
                ->all(),
        ], __('api.duas_ready'));
    }

    public function show(Request $request, Dua $dua): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if (! $dua->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $dua->loadCount('favorites');

        return $this->success(
            (new DuaResource($dua, $customer))->resolve(),
            __('api.success')
        );
    }

    public function favorite(Request $request, Dua $dua): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if (! $dua->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $customer->favoriteDuas()->syncWithoutDetaching([$dua->id]);

        return $this->success(
            (new DuaResource($dua->fresh(), $customer))->resolve(),
            __('api.dua_favorited')
        );
    }

    public function unfavorite(Request $request, Dua $dua): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        $customer->favoriteDuas()->detach($dua->id);

        return $this->success(
            (new DuaResource($dua->fresh(), $customer))->resolve(),
            __('api.dua_unfavorited')
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
