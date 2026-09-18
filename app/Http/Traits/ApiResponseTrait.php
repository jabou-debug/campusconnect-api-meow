<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a successful JSON response.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a 201 Created JSON response.
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Created successfully.'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(
        string $message = 'An error occurred.',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a 403 Forbidden JSON response.
     */
    protected function forbiddenResponse(
        string $message = 'You do not have permission to perform this action.'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a not found response.
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse("{$resource} not found.", 404);
    }
}