<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\QRCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class QRCodeFactory extends Factory
{
    protected $model = QRCode::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parameters' => [
                'size' => 300,
                'margin' => 10,
                'foreground' => '#000000',
                'background' => '#FFFFFF',
            ],
            'image_url' => null,
            'url' => fake()->url(),
        ];
    }
}
