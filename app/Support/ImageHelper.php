<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    /**
     * Resolve an image value to a public URL.
     *
     * - base64 data URI  → returned as-is
     * - absolute URL (http/https) → returned as-is
     * - relative storage path  → resolved to a public storage URL
     */
    public static function resolve(string $value): string
    {
        if (str_starts_with($value, 'data:') || str_starts_with($value, 'http')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    public static function storeBase64Image(string $base64String, string $directory): string
    {
        // Extraer el tipo de imagen y los datos
        preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);

        $extension = $matches[1] ?? 'png';
        $imageData = substr($base64String, strpos($base64String, ',') + 1);
        $decodedImage = base64_decode($imageData);

        // Generar nombre único
        $filename = Str::random(40).'.'.$extension;
        $path = $directory.'/'.$filename;

        // Guardar en storage
        Storage::disk('public')->put($path, $decodedImage);

        return $path;
    }
}
