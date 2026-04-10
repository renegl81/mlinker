<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'price' => '0.00',
                'period' => 'month',
                'description' => 'Para empezar sin coste',
                'is_active' => true,
                'stripe_price_id' => null,
                'max_locations' => 1,
                'max_menus_per_location' => 1,
                'max_products' => 25,
                'max_images' => 0,
                'has_analytics' => false,
                'has_custom_qr' => false,
                'has_multilang' => false,
                'has_catalog' => false,
                'has_team' => false,
                'has_api_access' => false,
                'has_custom_domain' => false,
                'show_branding' => true,
                'trial_days' => 0,
                'sort_order' => 0,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'price' => '14.99',
                'period' => 'month',
                'description' => 'Para negocios en crecimiento',
                'is_active' => true,
                'stripe_price_id' => null,
                'max_locations' => 3,
                'max_menus_per_location' => 5,
                'max_products' => 0,
                'max_images' => 50,
                'has_analytics' => true,
                'has_custom_qr' => true,
                'has_multilang' => false,
                'has_catalog' => false,
                'has_team' => true,
                'has_api_access' => false,
                'has_custom_domain' => false,
                'show_branding' => false,
                'trial_days' => 14,
                'sort_order' => 1,
            ],
            [
                'slug' => 'business',
                'name' => 'Business',
                'price' => '34.99',
                'period' => 'month',
                'description' => 'Para cadenas y grupos',
                'is_active' => true,
                'stripe_price_id' => null,
                'max_locations' => 10,
                'max_menus_per_location' => 0,
                'max_products' => 0,
                'max_images' => 0,
                'has_analytics' => true,
                'has_custom_qr' => true,
                'has_multilang' => true,
                'has_catalog' => true,
                'has_team' => true,
                'has_api_access' => false,
                'has_custom_domain' => true,
                'show_branding' => false,
                'trial_days' => 14,
                'sort_order' => 2,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'price' => '99.99',
                'period' => 'month',
                'description' => 'Para grandes corporaciones',
                'is_active' => true,
                'stripe_price_id' => null,
                'max_locations' => 0,
                'max_menus_per_location' => 0,
                'max_products' => 0,
                'max_images' => 0,
                'has_analytics' => true,
                'has_custom_qr' => true,
                'has_multilang' => true,
                'has_catalog' => true,
                'has_team' => true,
                'has_api_access' => true,
                'has_custom_domain' => true,
                'show_branding' => false,
                'trial_days' => 0,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
