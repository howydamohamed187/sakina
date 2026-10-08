<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DhikrResource;
use App\Models\Customer;
use App\Models\Dhikr;
use App\Support\CustomerFavorites;
use App\Support\DhikrCategories;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DhikrController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $customer = $this->customer($request);

        if (! $customer instanceof Customer) {
            return $customer;
        }

        if ($request->filled('category') && ! $request->filled('category_id')) {
            $request->merge(['category_id' => $request->query('category')]);
        }

        $filters = $request->validate([
            'category_id' => ['nullable', 'string', Rule::in(DhikrCategories::all())],
        ]);

        $search = $request->query('q', $request->query('search', ''));
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $category = $filters['category_id'] ?? null;

        $paginator = Dhikr::query()
            ->active()
            ->with(['favorites' => fn ($query) => $query->where('customer_id', $customer->id)])
            ->withCount('favorites')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('body', 'like', '%'.$search.'%')))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->ordered()
            ->paginate(min(max($request->integer('per_page', 15), 1), 50))
            ->withQueryString();

        return $this->paginatedWithMeta(
            [
                'favorites' => CustomerFavorites::adhkar($customer),
                'adhkar' => $paginator->getCollection()
                    ->map(fn (Dhikr $dhikr): array => (new DhikrResource($dhikr, $customer))->resolve())
                    ->all(),
            ],
            $paginator,
            __('api.adhkar_ready'),
        );
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
            (new DhikrResource($dhikr, $customer, detailed: true))->resolve(),
            __('api.success')
        );
    }

    public function categories(): JsonResponse
    {
        return $this->success(
            collect(DhikrCategories::all())
                ->map(fn (string $category): array => ['id' => $category, 'name' => DhikrCategories::label($category)])
                ->all(),
            __('api.adhkar_categories_ready'),
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
