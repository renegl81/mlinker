<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class ApiMenuController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $menus = Menu::orderBy('id')->get();

        return MenuResource::collection($menus);
    }

    public function show(Menu $menu): MenuResource
    {
        $menu->load([
            'sections' => fn ($q) => $q->orderBy('id'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
            'sections.products.allergens',
            'sections.products.ingredients',
        ]);

        return new MenuResource($menu);
    }

    public function qrCode(Menu $menu): JsonResponse
    {
        $qrCode = $menu->qrCode;

        return response()->json([
            'url' => $qrCode?->url,
            'image_url' => $qrCode?->image_url ? Storage::url($qrCode->image_url) : null,
        ]);
    }

    public function publicShow(int $id): MenuResource|JsonResponse
    {
        $menu = Menu::withoutGlobalScopes()->find($id);

        if (! $menu || ! $menu->is_active) {
            return response()->json(['error' => 'Menu not found'], 404);
        }

        $menu->load([
            'sections' => fn ($q) => $q->orderBy('id'),
            'sections.products' => fn ($q) => $q->orderBy('products.id'),
            'sections.products.allergens',
            'sections.products.ingredients',
        ]);

        $etag = md5((string) $menu->updated_at);

        return (new MenuResource($menu))
            ->response()
            ->withHeaders([
                'Cache-Control' => 'public, max-age=300',
                'ETag' => $etag,
            ]);
    }
}
