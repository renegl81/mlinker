<?php

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AllergenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $euAllergens = [
            'Cereales que contienen gluten',
            'Crustáceos',
            'Huevos',
            'Pescado',
            'Cacahuetes',
            'Soja',
            'Leche (incluida la lactosa)',
            'Frutos de cáscara (nueces)',
            'Apio',
            'Mostaza',
            'Sésamo',
            'Dióxido de azufre y sulfitos',
            'Altramuces',
            'Moluscos',
        ];

        $hasName = Schema::hasColumn('allergens', 'name');
        $hasSlug = Schema::hasColumn('allergens', 'slug');
        $hasMandatory = Schema::hasColumn('allergens', 'mandatory') ||
            Schema::hasColumn('allergens', 'is_mandatory') ||
            Schema::hasColumn('allergens', 'required');

        foreach ($euAllergens as $name) {
            $data = [];

            if ($hasName) {
                $data['name'] = $name;
            }

            if ($hasSlug) {
                $data['slug'] = Str::slug($name, '-');
            }

            if ($hasMandatory) {
                if (Schema::hasColumn('allergens', 'mandatory')) {
                    $data['mandatory'] = true;
                } elseif (Schema::hasColumn('allergens', 'is_mandatory')) {
                    $data['is_mandatory'] = true;
                } else {
                    $data['required'] = true;
                }
            }

            // Use name as unique key to be safe across schemas
            Allergen::updateOrCreate(['name' => $name], $data);
        }
    }
}
