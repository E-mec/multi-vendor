<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

if (!function_exists('successResponse')) {
    function successResponse( string $message = 'Success', $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}


if (!function_exists('failureResponse')) {
    function failureResponse(string $message = 'error', int $status = 400, $errors = []): JsonResponse
    {
        return response()->json([
            'status' => 'failure',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}

if (!function_exists('customException')) {
    function customException(Throwable $exception, string $context = ''): JsonResponse
    {
        Log::error("{$context} Exception", [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return failureResponse('An unexpected error occurred', 500);
    }
}

