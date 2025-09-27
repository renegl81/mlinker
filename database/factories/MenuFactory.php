<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Menu;
use App\Models\MenuCard;

class MenuFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'menu_card_id' => MenuCard::factory(),
            'image_url' => fake()->regexify('[A-Za-z0-9]{255}'),
            'show_prices' => fake()->boolean(),
            'show_currency' => fake()->boolean(),
            'show_calories' => fake()->boolean(),
        ];
    }
}
