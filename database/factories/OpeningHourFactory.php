<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\OpeningHour;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpeningHourFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OpeningHour::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'weekday' => fake()->numberBetween(-10000, 10000),
            'opens_at' => fake()->time(),
            'closes_at' => fake()->time(),
            'is_closed' => fake()->boolean(),
        ];
    }
}
