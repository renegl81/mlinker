<?php

namespace Database\Factories;

use App\Models\Translatable;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'translatable_id' => Translatable::factory(),
            'translatable_type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'locale' => fake()->regexify('[A-Za-z0-9]{10}'),
            'field' => fake()->regexify('[A-Za-z0-9]{50}'),
            'value' => fake()->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
