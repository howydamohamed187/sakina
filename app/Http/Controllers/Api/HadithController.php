<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\HadithResource;
use App\Models\Customer;
use App\Models\Hadith;
use App\Support\CustomerFavorites;
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

        $search = $request->query('q', $request->query('search', ''));
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';

        $paginator = Hadith::query()
            ->active()
            ->withFavoriteFor($customer)
            ->withCount('favorites')
            ->when($search !== '', fn ($query) => $query->search($search))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(min(max($request->integer('per_page', 15), 1), 50))
            ->withQueryString();

        return $this->paginatedWithMeta(
            [
                'favorites' => CustomerFavorites::hadiths($customer),
                'hadiths' => $paginator->getCollection()
                    ->map(fn (Hadith $hadith): array => (new HadithResource($hadith, $customer))->resolve())
                    ->all(),
            ],
            $paginator,
            __('api.hadiths_ready'),
        );
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
            (new HadithResource($hadith, $customer, detailed: true))->resolve(),
            __('api.success')
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
