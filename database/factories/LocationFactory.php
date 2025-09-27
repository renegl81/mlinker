<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\;
use App\Models\Location;
use App\Models\User;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'address' => fake()->regexify('[A-Za-z0-9]{255}'),
            'city' => fake()->city(),
            'province' => fake()->regexify('[A-Za-z0-9]{255}'),
            'postal_code' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'description' => fake()->text(),
            'user_id' => User::factory(),
            'country_id' => ::factory(),
            'image_url' => fake()->regexify('[A-Za-z0-9]{255}'),
            'logo_url' => fake()->regexify('[A-Za-z0-9]{255}'),
            'slug' => fake()->slug(),
            'url' => fake()->url(),
            'lang' => fake()->regexify('[A-Za-z0-9]{255}'),
            'languages' => '{}',
            'currency' => fake()->regexify('[A-Za-z0-9]{255}'),
            'time_format' => fake()->regexify('[A-Za-z0-9]{255}'),
            'time_zone' => fake()->regexify('[A-Za-z0-9]{255}'),
            'social_medias' => '{}',
        ];
    }
}
