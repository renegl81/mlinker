<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\MenuCard;
use App\Models\QRCode;

class QRCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = QRCode::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'menu_card_id' => MenuCard::factory(),
            'parameters' => '{}',
            'image_url' => fake()->regexify('[A-Za-z0-9]{255}'),
            'url' => fake()->url(),
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
