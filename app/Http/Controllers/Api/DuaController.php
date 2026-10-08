<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DuaResource;
use App\Models\Customer;
use App\Models\Dua;
use App\Support\CustomerFavorites;
use App\Support\DuaCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DuaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if ($request->boolean('simple')) {
            return $this->success(
                Dua::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(['id', 'title'])
                    ->map(fn (Dua $dua): array => ['id' => $dua->id, 'name' => $dua->title])
                    ->all(),
                __('api.duas_ready'),
            );
        }

        if ($request->filled('category') && ! $request->filled('category_id')) {
            $request->merge(['category_id' => $request->query('category')]);
        }

        $filters = $request->validate([
            'category_id' => ['nullable', 'string', Rule::in(DuaCategories::all())],
        ]);

        $search = $request->query('q', $request->query('search', ''));
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $category = $filters['category_id'] ?? null;

        $paginator = Dua::query()
            ->active()
            ->with(['favorites' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->withCount('favorites')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('body', 'like', '%'.$search.'%')))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(min(max($request->integer('per_page', 15), 1), 50))
            ->withQueryString();

        return $this->paginatedWithMeta(
            [
                'favorites' => CustomerFavorites::duas($customer),
                'duas' => $paginator->getCollection()
                    ->map(fn (Dua $dua): array => (new DuaResource($dua, $customer))->resolve())
                    ->all(),
            ],
            $paginator,
            __('api.duas_ready'),
        );
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
            (new DuaResource($dua, $customer, detailed: true))->resolve(),
            __('api.success')
        );
    }

    public function categories(): JsonResponse
    {
        return $this->success(
            collect(DuaCategories::all())
                ->map(fn (string $category): array => ['id' => $category, 'name' => DuaCategories::label($category)])
                ->all(),
            __('api.dua_categories_ready'),
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
