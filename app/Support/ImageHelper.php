<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper
{
    public static function storeBase64Image(string $base64String, string $directory): string
    {
        // Extraer el tipo de imagen y los datos
        preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);

        $extension = $matches[1] ?? 'png';
        $imageData = substr($base64String, strpos($base64String, ',') + 1);
        $decodedImage = base64_decode($imageData);

        // Generar nombre único
        $filename = Str::random(40) . '.' . $extension;
        $path = $directory . '/' . $filename;

        // Guardar en storage
        Storage::disk('public')->put($path, $decodedImage);

        return $path;
    }
}
