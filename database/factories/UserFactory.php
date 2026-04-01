<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->text(),
            'city' => fake()->city(),
            'province' => fake()->word(),
            'postal_code' => fake()->postcode(),
            'country_id' => Country::factory(),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::query()->firstOrCreate(['name' => 'Admin']);

            if (! $user->roles()->whereKey($role->id)->exists()) {
                $user->roles()->attach($role);
            }
        });
    }
}
