<?php

use Illuminate\Http\JsonResponse;

if (!function_exists('apiResponse')) {
    function apiResponse(mixed $data, string $message = 'success!', int $code = 200): JsonResponse {
        return Response::json([
            'status' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
