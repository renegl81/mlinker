<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;

class UeAllergenSeeder extends Seeder
{
    /**
     * The 14 mandatory EU allergens.
     * Each entry: [name, code, description]
     */
    public static array $allergens = [
        ['name' => 'Gluten', 'code' => 'gluten', 'description' => 'Cereales que contienen gluten (trigo, centeno, cebada, avena, espelta, kamut)'],
        ['name' => 'Crustáceos', 'code' => 'crustaceans', 'description' => 'Crustáceos y productos a base de crustáceos'],
        ['name' => 'Huevos', 'code' => 'eggs', 'description' => 'Huevos y productos a base de huevo'],
        ['name' => 'Pescado', 'code' => 'fish', 'description' => 'Pescado y productos a base de pescado'],
        ['name' => 'Cacahuetes', 'code' => 'peanuts', 'description' => 'Cacahuetes y productos a base de cacahuetes'],
        ['name' => 'Soja', 'code' => 'soy', 'description' => 'Soja y productos a base de soja'],
        ['name' => 'Lácteos', 'code' => 'dairy', 'description' => 'Leche y productos lácteos (incluida la lactosa)'],
        ['name' => 'Frutos secos', 'code' => 'nuts', 'description' => 'Almendras, avellanas, nueces, anacardos, pacanas, nueces de Brasil, pistachos, macadamias'],
        ['name' => 'Apio', 'code' => 'celery', 'description' => 'Apio y productos derivados'],
        ['name' => 'Mostaza', 'code' => 'mustard', 'description' => 'Mostaza y productos derivados'],
        ['name' => 'Sésamo', 'code' => 'sesame', 'description' => 'Granos de sésamo y productos a base de sésamo'],
        ['name' => 'Sulfitos', 'code' => 'sulfites', 'description' => 'Dióxido de azufre y sulfitos en concentraciones superiores a 10 mg/kg o 10 mg/litro'],
        ['name' => 'Altramuces', 'code' => 'lupin', 'description' => 'Altramuces y productos a base de altramuces'],
        ['name' => 'Moluscos', 'code' => 'molluscs', 'description' => 'Moluscos y productos a base de moluscos'],
    ];

    /**
     * Seed the 14 EU allergens for a specific tenant.
     * Idempotent — uses updateOrCreate on (tenant_id, name).
     */
    public static function seedForTenant(string $tenantId): void
    {
        foreach (self::$allergens as $allergen) {
            Allergen::updateOrCreate(
                ['tenant_id' => $tenantId, 'name' => $allergen['name']],
                [
                    'code' => $allergen['code'],
                    'description' => $allergen['description'],
                ]
            );
        }
    }

    public function run(): void
    {
        // When run via artisan, seed for the current tenant context
        $tenantId = tenant('id');
        if ($tenantId) {
            self::seedForTenant($tenantId);
        }
    }
}
