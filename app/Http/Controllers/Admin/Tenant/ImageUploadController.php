<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Plan\CheckLimit;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function store(ImageUploadRequest $request): JsonResponse
    {
        (new CheckLimit)->execute('images', throw: true);

        $file = $request->file('image');
        $tenantId = tenant('id');

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $directory = "tenant{$tenantId}/images";
        $thumbDirectory = "tenant{$tenantId}/images/thumbs";

        Storage::disk('public')->putFileAs($directory, $file, $filename);

        $this->generateThumbnail(
            $file->getRealPath(),
            $file->getClientOriginalExtension(),
            $thumbDirectory.'/'.$filename
        );

        return response()->json([
            'url' => "{$directory}/{$filename}",
            'thumbnail_url' => "{$thumbDirectory}/{$filename}",
        ]);
    }

    private function generateThumbnail(string $sourcePath, string $extension, string $storagePath): void
    {
        $thumbWidth = 300;
        $thumbHeight = 300;

        $ext = strtolower($extension);

        $source = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
            'png' => @imagecreatefrompng($sourcePath),
            'webp' => @imagecreatefromwebp($sourcePath),
            default => false,
        };

        if ($source === false) {
            return;
        }

        $origWidth = imagesx($source);
        $origHeight = imagesy($source);

        // Cover: recortar al centro para llenar 300x300
        $srcRatio = $origWidth / $origHeight;
        $dstRatio = $thumbWidth / $thumbHeight;

        if ($srcRatio > $dstRatio) {
            $srcH = $origHeight;
            $srcW = (int) round($origHeight * $dstRatio);
            $srcX = (int) round(($origWidth - $srcW) / 2);
            $srcY = 0;
        } else {
            $srcW = $origWidth;
            $srcH = (int) round($origWidth / $dstRatio);
            $srcX = 0;
            $srcY = (int) round(($origHeight - $srcH) / 2);
        }

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        if ($ext === 'png') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefill($thumb, 0, 0, $transparent);
        }

        imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, $thumbWidth, $thumbHeight, $srcW, $srcH);

        ob_start();
        match ($ext) {
            'jpg', 'jpeg' => imagejpeg($thumb, null, 85),
            'png' => imagepng($thumb),
            'webp' => imagewebp($thumb, null, 85),
            default => imagejpeg($thumb, null, 85),
        };
        $thumbData = ob_get_clean();

        imagedestroy($source);
        imagedestroy($thumb);

        Storage::disk('public')->put($storagePath, $thumbData);
    }
}
