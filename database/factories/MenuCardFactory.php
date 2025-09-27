<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\;
use App\Models\Location;
use App\Models\MenuCard;

class MenuCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MenuCard::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'location_id' => Location::factory(),
            'image_url' => fake()->regexify('[A-Za-z0-9]{255}'),
            'template_id' => ::factory(),
        ];
    }
}
