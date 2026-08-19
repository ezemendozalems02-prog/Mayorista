<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if (! is_null($data)) {
            $payload['data'] = $data;
        }

        return response()->json($payload, $status);
    }

    protected function error(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    protected function notFound(string $message = 'Recurso no encontrado.'): JsonResponse
    {
        return $this->error($message, [], 404);
    }

    protected function forbidden(string $message = 'No tiene permisos para realizar esta acción.'): JsonResponse
    {
        return $this->error($message, [], 403);
    }
}
