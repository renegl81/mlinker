<?php

namespace App\Actions\Menu;

use App\Models\Menu;
use App\Support\ImageHelper;
use Illuminate\Support\Facades\Storage;

class UpdateMenu
{
    public function execute(Menu $menu, array $data): Menu
    {
        $menuData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? $menu->description,
            'template_id' => $data['template_id'] ?? $menu->template_id,
            'is_active' => $data['is_active'] ?? true,
            'show_currency' => $data['show_currency'] ?? false,
            'show_prices' => $data['show_prices'] ?? true,
            'show_calories' => $data['show_calories'] ?? false,
        ];

        // Image handling:
        // - array_key_exists: distinguish "not sent" from "sent as null"
        // - base64 → store new image, delete old one
        // - null → user removed the image
        // - absolute URL (starts with http) → pre-resolved URL from image_path
        //   accessor, means the user did not change the image → do not touch
        if (array_key_exists('image_url', $data)) {
            $incoming = $data['image_url'];

            if (is_string($incoming) && str_starts_with($incoming, 'data:image')) {
                if ($menu->image_url) {
                    Storage::disk('public')->delete($menu->image_url);
                }
                $menuData['image_url'] = ImageHelper::storeBase64Image($incoming, 'menus');
            } elseif ($incoming === null) {
                if ($menu->image_url) {
                    Storage::disk('public')->delete($menu->image_url);
                }
                $menuData['image_url'] = null;
            }
            // If it's an http(s) URL or anything else, we intentionally do not
            // touch the existing image_url column.
        }

        $menu->update($menuData);

        return $menu->fresh();
    }
}
