<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => ::factory(),
            'started_at' => fake()->dateTime(),
            'ends_at' => fake()->dateTime(),
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
        ];
    }
}
