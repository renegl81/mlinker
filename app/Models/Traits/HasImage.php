<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Storage;

trait HasImage
{
    public static function bootHasImage(): void
    {
        static::deleting(function (self $model): void {
            $imageUrl = $model->image_url ?? null;

            if ($imageUrl === null) {
                return;
            }

            // Solo borrar archivos de storage local (paths relativos)
            if (str_starts_with($imageUrl, 'data:') || str_starts_with($imageUrl, 'http')) {
                return;
            }

            Storage::disk('public')->delete($imageUrl);

            $thumbPath = dirname($imageUrl).'/thumbs/'.basename($imageUrl);
            Storage::disk('public')->delete($thumbPath);
        });
    }
}
