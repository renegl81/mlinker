<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Restaurante',
            'Cafetería',
            'Bar',
            'Tasca',
            'Pub',
            'Cervecería',
            'Bodega',
            'Pizzería',
            'Pastelería',
            'Heladería',
            'Food truck',
            'Buffet',
            'Brasería',
            'Mesón',
            'Taberna',
            'Chiringuito',
            'Gastrobar',
            'Coctelería',
            'Vinoteca',
            'Parrilla',
            'Asador',
            'Restaurante vegetariano',
            'Restaurante vegano',
            'Casa de comidas'
        ];
        foreach ($names as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}
