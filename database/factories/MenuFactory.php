<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Menu;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'location_id' => Location::factory(),
            'template_id' => Template::factory(),
            'image_url' => null,
            'is_active' => true,
            'show_prices' => true,
            'show_currency' => true,
            'show_calories' => false,
        ];
    }
}
