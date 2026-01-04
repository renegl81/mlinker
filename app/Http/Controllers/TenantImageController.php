<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TenantImageController extends Controller
{
    /**
     * Serve tenant-specific images from storage.
     */
    public function __invoke(string $tenant, string $path): BinaryFileResponse
    {
        // Decodificar el path por si tiene caracteres especiales
        $path = urldecode($path);
        $filePath = storage_path("{$tenant}/app/public/{$path}");

        if (!File::exists($filePath)) {
            abort(404);
        }

        // Obtener el mime type correcto
        $mimeType = File::mimeType($filePath);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
        ]);
    }
}
