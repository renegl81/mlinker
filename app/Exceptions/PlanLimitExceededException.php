<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlanLimitExceededException extends Exception
{
    public function __construct(string $resource, int $max)
    {
        $label = match ($resource) {
            'locations' => 'locales',
            'menus' => 'menús',
            'products' => 'productos',
            'images' => 'imágenes',
            default => $resource,
        };

        $message = $max > 0
            ? "Has alcanzado el límite de {$max} {$label} de tu plan actual."
            : "Has alcanzado el límite de {$label} de tu plan actual.";

        parent::__construct($message, Response::HTTP_FORBIDDEN);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    public function render(Request $request): JsonResponse|false
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        return false;
    }
}
