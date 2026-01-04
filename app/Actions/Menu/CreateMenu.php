<?php

namespace App\Actions\Menu;

use App\Models\Menu;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateMenu
{
    public function execute(array $data): Menu
    {
        $menuData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'show_currency' => $data['show_currency'] ?? false,
            'show_prices' => $data['show_prices'] ?? true,
            'show_calories' => $data['show_calories'] ?? false,
        ];

        if (isset($data['image_url']) && $data['image_url'] instanceof UploadedFile) {
            $menuData['image_url'] = $data['image_url']->store('menus', 'public');
        }

        return Menu::create($menuData);
    }
}
