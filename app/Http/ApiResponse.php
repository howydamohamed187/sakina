<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function json(int $status, string $message, mixed $data = null, array $errors = []): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'errors' => self::errors($errors),
            'data' => $data ?? (object) [],
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function errors(array $errors): object
    {
        $flat = [];

        foreach ($errors as $field => $value) {
            $flat[$field] = is_array($value) ? (string) ($value[0] ?? '') : (string) $value;
        }

        return (object) $flat;
    }
}
