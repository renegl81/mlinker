<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // Slug column is varchar(30); build a short unique slug that fits.
            'slug' => 'plan-'.Str::lower(Str::random(12)),
            'price' => fake()->randomFloat(2, 0, 999999.99),
            'period' => 'month',
            'description' => fake()->text(),
            'is_active' => true,
            'max_locations' => 1,
            'max_menus_per_location' => 1,
            'max_products' => 25,
            'max_images' => 0,
            'has_analytics' => false,
            'has_custom_qr' => false,
            'has_multilang' => false,
            'has_api_access' => false,
            'has_custom_domain' => false,
            'show_branding' => true,
            'trial_days' => 0,
            'sort_order' => 0,
        ];
    }
}
