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
            'description' => $data['description'],
            'template_id' => $data['template_id'],
            'is_active' => $data['is_active'] ?? true,
            'show_currency' => $data['show_currency'] ?? false,
            'show_prices' => $data['show_prices'] ?? true,
            'show_calories' => $data['show_calories'] ?? false,
        ];

        if (isset($data['image_url']) && is_string($data['image_url']) && str_starts_with($data['image_url'], 'data:image')) {
            // Eliminar imagen anterior si existe
            if ($menu->image_url) {
                Storage::disk('public')->delete($menu->image_url);
            }

            $menuData['image_url'] = ImageHelper::storeBase64Image($data['image_url'], 'menus');
        }

        $menu->update($menuData);

        return $menu->fresh();
    }
}
