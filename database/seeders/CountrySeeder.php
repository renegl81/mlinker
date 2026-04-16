<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'Spain',
                'code' => 'ES',
            ],
            [
                'name' => 'France',
                'code' => 'FR',
            ],
            [
                'name' => 'Germany',
                'code' => 'DE',
            ],
            [
                'name' => 'Italy',
                'code' => 'IT',
            ],
            [
                'name' => 'Portugal',
                'code' => 'PT',
            ],
        ];
        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                ['name' => $country['name']],
            );
        }
    }
}
