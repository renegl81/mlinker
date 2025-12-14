<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'amount' => fake()->randomFloat(2, 0, 999999.99),
            'paid_at' => fake()->dateTime(),
            'payment_method' => fake()->regexify('[A-Za-z0-9]{30}'),
            'status' => fake()->regexify('[A-Za-z0-9]{20}'),
        ];
    }
}
