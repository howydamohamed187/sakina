<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ToggleFavoriteRequest;
use App\Models\Customer;
use App\Support\FavoriteTypes;
use Illuminate\Http\JsonResponse;

class FavoriteController extends ApiController
{
    /**
     * One endpoint for every content type: saves the item if it is not in the customer's
     * favorites, removes it otherwise. Removing still works after the item was deactivated.
     */
    public function toggle(ToggleFavoriteRequest $request): JsonResponse
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            return $this->error(__('api.not_found'), [], 403);
        }

        $type = $request->favoriteType();
        $id = (int) $request->validated('id');
        $item = FavoriteTypes::model($type)::query()->find($id);

        if (! $item) {
            return $this->error(__('api.not_found'), [], 404);
        }

        if ($item->favorites()->where('customer_id', $customer->id)->delete() > 0) {
            return $this->result($type, $id, false);
        }

        if (! $item->isActive()) {
            return $this->error(__('api.not_found'), [], 404);
        }

        $item->favorites()->createOrFirst(['customer_id' => $customer->id]);

        return $this->result($type, $id, true);
    }

    private function result(string $type, int $id, bool $isFavorite): JsonResponse
    {
        return $this->success(
            ['type' => $type, 'id' => $id, 'is_favorite' => $isFavorite],
            FavoriteTypes::message($type, $isFavorite),
        );
    }
}
