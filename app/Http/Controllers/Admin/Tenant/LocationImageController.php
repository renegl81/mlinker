<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocationImageController extends Controller
{
    public function store(Request $request, Location $location)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:3072'],
        ]);

        $currentCount = LocationImage::where('location_id', $location->id)->count();
        if ($currentCount >= 5) {
            return back()->with('error', 'Máximo 5 imágenes por local.');
        }

        $path = $request->file('image')->store("locations/{$location->id}/gallery", 'public');

        LocationImage::create([
            'location_id' => $location->id,
            'tenant_id' => tenant()->id,
            'path' => Storage::disk('public')->url($path),
            'sort_order' => $currentCount,
        ]);

        return back()->with('success', 'Imagen subida.');
    }

    public function destroy(LocationImage $locationImage)
    {
        if ($locationImage->path) {
            Storage::disk('public')->delete($locationImage->path);
        }
        $locationImage->delete();

        return back()->with('success', 'Imagen eliminada.');
    }
}
