<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TasbeehResource;
use App\Models\Customer;
use App\Models\Dhikr;
use App\Services\TasbeehProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TasbeehController extends ApiController
{
    public function __construct(private readonly TasbeehProgressService $progress) {}

    public function index(Request $request): JsonResponse
    {
        $adhkar = Dhikr::query()
            ->active()
            ->countable()
            ->ordered()
            ->with(['progress' => $this->scopeToCustomer($request)])
            ->get();

        return $this->success(
            TasbeehResource::collection($adhkar)->resolve(),
            __('api.tasbeehs_ready')
        );
    }

    public function show(Request $request, Dhikr $dhikr): JsonResponse
    {
        if (! $dhikr->isAvailableForTasbeeh()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $dhikr->load(['progress' => $this->scopeToCustomer($request)]);

        return $this->success((new TasbeehResource($dhikr))->resolve(), __('api.success'));
    }

    public function progress(Request $request, Dhikr $dhikr): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        if (! $dhikr->isAvailableForTasbeeh()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $data = $request->validate([
            'current_count' => ['required', 'integer', 'min:0', 'max:'.$dhikr->target_count],
            'total_count' => ['required', 'integer', 'gte:current_count', 'max:100000000'],
        ]);

        $progress = $this->progress->sync($customer, $dhikr, (int) $data['current_count'], (int) $data['total_count']);

        $dhikr->setRelation('progress', collect([$progress]));

        return $this->success((new TasbeehResource($dhikr))->resolve(), __('api.tasbeeh_progress_saved'));
    }

    private function scopeToCustomer(Request $request): \Closure
    {
        $user = $request->user();
        $customerId = $user instanceof Customer ? $user->id : 0;

        return fn ($query) => $query->where('customer_id', $customerId);
    }
}
