<?php

namespace App\Http\Requests\Api;

use App\Support\FavoriteTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The customer always comes from the auth token; any user_id in the body is ignored.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(FavoriteTypes::accepted())],
            'id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function favoriteType(): string
    {
        return (string) FavoriteTypes::normalize($this->validated('type'));
    }
}
