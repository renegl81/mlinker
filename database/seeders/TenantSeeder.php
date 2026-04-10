<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $domainSuffix = config('app.domain', 'lvh.me');
        $ownerRoleId = $this->ensureRole('Owner');

        $countryIds = DB::table('countries')->pluck('id', 'code');
        $categoryIds = DB::table('categories')->pluck('id', 'name');
        $templateIds = DB::table('templates')->where('is_active', true)->pluck('id')->values();

        $owners = [
            ['Nora', 'Luna'], ['Mateo', 'Gil'], ['Alba', 'Vega'], ['Izan', 'Rios'], ['Noa', 'Paredes'],
            ['Lucia', 'Mora'], ['Leo', 'Delgado'], ['Carmen', 'Santos'], ['Bruno', 'Fuentes'], ['Aitana', 'Nadal'],
        ];

        $tenantProfiles = $this->tenantProfiles();

        foreach ($tenantProfiles as $index => $profile) {
            $tenantId = Str::slug($profile['name']);
            $ownerName = $owners[$index % count($owners)];
            $ownerEmail = $tenantId.'@'.$domainSuffix;

            $ownerId = DB::table('users')->updateOrInsert(
                ['email' => $ownerEmail],
                [
                    'name' => $ownerName[0],
                    'last_name' => $ownerName[1],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            )
                ? DB::table('users')->where('email', $ownerEmail)->value('id')
                : DB::table('users')->where('email', $ownerEmail)->value('id');

            DB::table('role_user')->updateOrInsert(
                ['user_id' => $ownerId, 'role_id' => $ownerRoleId],
                []
            );

            $businessType = $this->mapBusinessType($profile['type'] ?? 'restaurant');
            $homeTemplate = config("menulinker.default_home_template.{$businessType}", 'HomeClassic');

            DB::table('tenants')->updateOrInsert(
                ['id' => $tenantId],
                [
                    'stripe_id' => 'cus_'.str_pad((string) ($index + 1000), 10, '0', STR_PAD_LEFT),
                    'pm_type' => 'card',
                    'pm_last_four' => (string) random_int(1000, 9999),
                    'trial_ends_at' => now()->addDays(14),
                    'stripe_connect_id' => 'acct_'.str_pad((string) ($index + 7000), 10, '0', STR_PAD_LEFT),
                    'data' => json_encode(['business_type' => $profile['type'], 'cuisine' => $profile['cuisine']]),
                    'has_website'   => true,
                    'business_type' => $businessType,
                    'home_template' => $homeTemplate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('domains')->updateOrInsert(
                ['domain' => $tenantId.'.'.$domainSuffix],
                [
                    'tenant_id' => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('tenant_user')->updateOrInsert(
                ['tenant_id' => $tenantId, 'user_id' => $ownerId],
                [
                    'role' => 'owner',
                    'is_active' => true,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->clearTenantCatalog($tenantId);

            $countryId = $countryIds[$profile['country']] ?? $countryIds->first();
            $templateId = $templateIds->isNotEmpty() ? $templateIds->random() : null;

            $locationId = DB::table('locations')->insertGetId([
                'name' => $profile['name'],
                'address' => $profile['address'],
                'city' => $profile['city'],
                'province' => $profile['province'],
                'postal_code' => (string) random_int(10000, 52999),
                'phone' => '+34 '.random_int(600, 799).' '.random_int(100000, 999999),
                'description' => $profile['description'],
                'user_id' => $ownerId,
                'country_id' => $countryId,
                'slug' => $tenantId,
                'url' => 'https://'.$tenantId.'.'.$domainSuffix,
                'lang' => 'es',
                'languages' => json_encode(['es', 'en']),
                'currency' => 'EUR',
                'time_format' => '24h',
                'time_zone' => 'Europe/Madrid',
                'social_medias' => json_encode([
                    'instagram' => '@'.str_replace('-', '', $tenantId),
                    'facebook' => $profile['name'],
                ]),
                'tenant_id' => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $categoryName = $profile['type'];
            if (isset($categoryIds[$categoryName])) {
                DB::table('category_location')->insert([
                    'category_id' => $categoryIds[$categoryName],
                    'location_id' => $locationId,
                    'tenant_id' => $tenantId,
                ]);
            }

            for ($weekday = 1; $weekday <= 7; $weekday++) {
                $isClosed = $weekday === 1;

                DB::table('opening_hours')->insert([
                    'location_id' => $locationId,
                    'weekday' => $weekday,
                    'opens_at' => $isClosed ? null : '13:00:00',
                    'closes_at' => $isClosed ? null : '23:30:00',
                    'is_closed' => $isClosed,
                    'tenant_id' => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $allergenIds = $this->seedAllergensForTenant($tenantId, $profile['short_name']);

            $menuCount = random_int(1, 3);
            $menuNames = $this->menuNamesFor($profile['type'], $menuCount);
            $catalog = $this->catalogByCuisine($profile['cuisine']);
            $ingredientIds = [];

            foreach ($menuNames as $menuName) {
                $menuId = DB::table('menus')->insertGetId([
                    'name' => $menuName,
                    'description' => 'Selección de '.$profile['short_name'].' con recetas caseras y producto de temporada.',
                    'location_id' => $locationId,
                    'template_id' => $templateId,
                    'show_prices' => true,
                    'show_currency' => true,
                    'show_calories' => true,
                    'is_active' => true,
                    'tenant_id' => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sectionIds = [];
                foreach (['Entrantes', 'Principales', 'Postres', 'Bebidas'] as $sectionName) {
                    $sectionIds[$sectionName] = DB::table('sections')->insertGetId([
                        'name' => $sectionName,
                        'description' => 'Sección '.$sectionName,
                        'menu_id' => $menuId,
                        'tenant_id' => $tenantId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $dishCount = random_int(10, 14);
                $selectedDishes = collect($catalog)->shuffle()->take($dishCount)->values();

                foreach ($selectedDishes as $dish) {
                    $productId = DB::table('products')->insertGetId([
                        'name' => $dish['name'],
                        'description' => $dish['description'],
                        'price' => $dish['price'],
                        'calories' => $dish['calories'],
                        'tenant_id' => $tenantId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('menu_product')->insert([
                        'menu_id' => $menuId,
                        'product_id' => $productId,
                        'tenant_id' => $tenantId,
                    ]);

                    $sectionName = $dish['section'];
                    DB::table('product_section')->insert([
                        'product_id' => $productId,
                        'section_id' => $sectionIds[$sectionName] ?? $sectionIds['Principales'],
                        'tenant_id' => $tenantId,
                    ]);

                    foreach ($dish['ingredients'] as $ingredient) {
                        if (! isset($ingredientIds[$ingredient])) {
                            $ingredientIds[$ingredient] = DB::table('ingredients')->insertGetId([
                                'name' => $ingredient.' ('.$profile['short_name'].')',
                                'description' => 'Ingrediente empleado en recetas de '.$profile['name'].'.',
                                'tenant_id' => $tenantId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('ingredient_product')->insert([
                            'ingredient_id' => $ingredientIds[$ingredient],
                            'product_id' => $productId,
                            'tenant_id' => $tenantId,
                        ]);
                    }

                    foreach ($dish['allergens'] as $allergenKey) {
                        if (isset($allergenIds[$allergenKey])) {
                            DB::table('allergen_product')->insert([
                                'allergen_id' => $allergenIds[$allergenKey],
                                'product_id' => $productId,
                                'tenant_id' => $tenantId,
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function ensureRole(string $name): int
    {
        DB::table('roles')->updateOrInsert(['name' => $name], ['updated_at' => now(), 'created_at' => now()]);

        return (int) DB::table('roles')->where('name', $name)->value('id');
    }

    private function clearTenantCatalog(string $tenantId): void
    {
        DB::table('ingredient_product')->where('tenant_id', $tenantId)->delete();
        DB::table('allergen_product')->where('tenant_id', $tenantId)->delete();
        DB::table('product_section')->where('tenant_id', $tenantId)->delete();
        DB::table('menu_product')->where('tenant_id', $tenantId)->delete();
        DB::table('opening_hours')->where('tenant_id', $tenantId)->delete();
        DB::table('category_location')->where('tenant_id', $tenantId)->delete();
        DB::table('sections')->where('tenant_id', $tenantId)->delete();
        DB::table('products')->where('tenant_id', $tenantId)->delete();
        DB::table('menus')->where('tenant_id', $tenantId)->delete();
        DB::table('ingredients')->where('tenant_id', $tenantId)->delete();
        DB::table('allergens')->where('tenant_id', $tenantId)->delete();
        DB::table('locations')->where('tenant_id', $tenantId)->delete();
    }

    private function seedAllergensForTenant(string $tenantId, string $shortName): array
    {
        $catalog = [
            'gluten' => ['Gluten', 'Cereales que contienen gluten.'],
            'crustaceos' => ['Crustáceos', 'Puede contener crustáceos.'],
            'huevos' => ['Huevos', 'Contiene huevo o derivados.'],
            'pescado' => ['Pescado', 'Contiene pescado o derivados.'],
            'cacahuetes' => ['Cacahuetes', 'Puede contener trazas de cacahuete.'],
            'soja' => ['Soja', 'Contiene soja.'],
            'lactosa' => ['Lactosa', 'Contiene leche o lactosa.'],
            'frutos_secos' => ['Frutos de cáscara', 'Puede contener frutos secos.'],
            'apio' => ['Apio', 'Contiene apio.'],
            'mostaza' => ['Mostaza', 'Contiene mostaza.'],
            'sesamo' => ['Sésamo', 'Contiene sésamo.'],
            'sulfitos' => ['Sulfitos', 'Contiene sulfitos.'],
            'altramuces' => ['Altramuces', 'Contiene altramuces.'],
            'moluscos' => ['Moluscos', 'Contiene moluscos.'],
        ];

        $ids = [];

        foreach ($catalog as $key => $values) {
            $id = DB::table('allergens')->insertGetId([
                'name' => $values[0].' ('.$shortName.')',
                'description' => $values[1],
                'tenant_id' => $tenantId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ids[$key] = $id;
        }

        return $ids;
    }

    private function menuNamesFor(string $type, int $count): array
    {
        $map = [
            'Cafetería' => ['Desayunos', 'Brunch', 'Cafés y Dulces'],
            'Pastelería' => ['Dulces del Día', 'Bollería Artesana', 'Bebidas Calientes'],
            'Heladería' => ['Helados', 'Copas y Postres', 'Bebidas Frías'],
            'Food truck' => ['Street Menu', 'Especiales del Camión', 'Bebidas'],
            'Pizzería' => ['Pizzas y Entrantes', 'Especiales de Horno', 'Postres y Bebidas'],
        ];

        $base = $map[$type] ?? ['Carta Principal', 'Sugerencias de Temporada', 'Bebidas y Postres'];

        return array_slice($base, 0, $count);
    }

    private function catalogByCuisine(string $cuisine): array
    {
        $catalog = [
            'italiana' => [
                ['name' => 'Bruschetta de tomate y albahaca', 'description' => 'Pan crujiente con tomate rallado, ajo y albahaca fresca.', 'price' => 6.90, 'calories' => 280, 'section' => 'Entrantes', 'ingredients' => ['Pan', 'Tomate', 'Ajo', 'Albahaca', 'Aceite de oliva'], 'allergens' => ['gluten']],
                ['name' => 'Carpaccio de ternera', 'description' => 'Láminas de ternera con rúcula, parmesano y limón.', 'price' => 11.50, 'calories' => 340, 'section' => 'Entrantes', 'ingredients' => ['Ternera', 'Rúcula', 'Parmesano', 'Limón', 'Aceite de oliva'], 'allergens' => ['lactosa']],
                ['name' => 'Pizza Margherita', 'description' => 'Salsa de tomate, mozzarella fior di latte y albahaca.', 'price' => 12.90, 'calories' => 780, 'section' => 'Principales', 'ingredients' => ['Harina de trigo', 'Tomate', 'Mozzarella', 'Albahaca', 'Aceite de oliva'], 'allergens' => ['gluten', 'lactosa']],
                ['name' => 'Pizza Quattro Formaggi', 'description' => 'Mozzarella, gorgonzola, parmesano y provolone.', 'price' => 14.50, 'calories' => 920, 'section' => 'Principales', 'ingredients' => ['Harina de trigo', 'Mozzarella', 'Gorgonzola', 'Parmesano', 'Provolone'], 'allergens' => ['gluten', 'lactosa']],
                ['name' => 'Lasagna della Nonna', 'description' => 'Lasaña de carne con bechamel y queso gratinado.', 'price' => 15.20, 'calories' => 860, 'section' => 'Principales', 'ingredients' => ['Pasta', 'Carne picada', 'Tomate', 'Bechamel', 'Queso'], 'allergens' => ['gluten', 'lactosa', 'huevos']],
                ['name' => 'Risotto de setas', 'description' => 'Arroz cremoso con boletus, parmesano y mantequilla.', 'price' => 14.80, 'calories' => 690, 'section' => 'Principales', 'ingredients' => ['Arroz arborio', 'Boletus', 'Parmesano', 'Mantequilla', 'Cebolla'], 'allergens' => ['lactosa']],
                ['name' => 'Tiramisu clásico', 'description' => 'Bizcocho de café, mascarpone y cacao puro.', 'price' => 6.80, 'calories' => 430, 'section' => 'Postres', 'ingredients' => ['Mascarpone', 'Café', 'Bizcocho', 'Cacao', 'Huevo'], 'allergens' => ['gluten', 'lactosa', 'huevos']],
                ['name' => 'Panna cotta de vainilla', 'description' => 'Panna cotta suave con coulis de frutos rojos.', 'price' => 6.50, 'calories' => 350, 'section' => 'Postres', 'ingredients' => ['Nata', 'Vainilla', 'Azúcar', 'Frutos rojos', 'Gelatina'], 'allergens' => ['lactosa']],
                ['name' => 'Limonada italiana', 'description' => 'Limonada fresca con hierbabuena y soda.', 'price' => 3.80, 'calories' => 110, 'section' => 'Bebidas', 'ingredients' => ['Limón', 'Soda', 'Azúcar', 'Hierbabuena'], 'allergens' => []],
                ['name' => 'Café espresso', 'description' => 'Espresso intenso de tueste italiano.', 'price' => 2.10, 'calories' => 5, 'section' => 'Bebidas', 'ingredients' => ['Café'], 'allergens' => []],
                ['name' => 'Gnocchi al pesto', 'description' => 'Gnocchi de patata con pesto de albahaca y piñones.', 'price' => 13.90, 'calories' => 710, 'section' => 'Principales', 'ingredients' => ['Patata', 'Harina', 'Albahaca', 'Piñones', 'Parmesano'], 'allergens' => ['gluten', 'lactosa', 'frutos_secos']],
                ['name' => 'Ensalada caprese', 'description' => 'Tomate, mozzarella, albahaca y reducción de balsámico.', 'price' => 9.20, 'calories' => 310, 'section' => 'Entrantes', 'ingredients' => ['Tomate', 'Mozzarella', 'Albahaca', 'Vinagre balsámico'], 'allergens' => ['lactosa', 'sulfitos']],
            ],
            'cafeteria' => [
                ['name' => 'Tostada de aguacate y huevo poché', 'description' => 'Pan de masa madre con aguacate, huevo poché y semillas.', 'price' => 7.40, 'calories' => 420, 'section' => 'Entrantes', 'ingredients' => ['Pan de masa madre', 'Aguacate', 'Huevo', 'Sésamo'], 'allergens' => ['gluten', 'huevos', 'sesamo']],
                ['name' => 'Croissant de mantequilla', 'description' => 'Croissant artesano recién horneado.', 'price' => 2.80, 'calories' => 280, 'section' => 'Postres', 'ingredients' => ['Harina de trigo', 'Mantequilla', 'Levadura', 'Azúcar'], 'allergens' => ['gluten', 'lactosa']],
                ['name' => 'Bowl de yogur y granola', 'description' => 'Yogur natural con granola casera y fruta fresca.', 'price' => 5.90, 'calories' => 330, 'section' => 'Postres', 'ingredients' => ['Yogur', 'Avena', 'Miel', 'Fruta fresca', 'Nueces'], 'allergens' => ['gluten', 'lactosa', 'frutos_secos']],
                ['name' => 'Sándwich club de pavo', 'description' => 'Pan tostado con pavo, bacon, lechuga y tomate.', 'price' => 8.20, 'calories' => 610, 'section' => 'Principales', 'ingredients' => ['Pan', 'Pavo', 'Bacon', 'Lechuga', 'Tomate'], 'allergens' => ['gluten']],
                ['name' => 'Wrap vegetal', 'description' => 'Tortilla de trigo con hummus, brotes y verduras asadas.', 'price' => 7.80, 'calories' => 470, 'section' => 'Principales', 'ingredients' => ['Tortilla de trigo', 'Hummus', 'Pimiento', 'Calabacín', 'Espinaca'], 'allergens' => ['gluten', 'sesamo']],
                ['name' => 'Cheesecake de frutos rojos', 'description' => 'Tarta de queso cremosa con salsa de frutos rojos.', 'price' => 5.80, 'calories' => 390, 'section' => 'Postres', 'ingredients' => ['Queso crema', 'Galleta', 'Mantequilla', 'Frutos rojos'], 'allergens' => ['gluten', 'lactosa']],
                ['name' => 'Café latte', 'description' => 'Espresso con leche texturizada.', 'price' => 2.90, 'calories' => 130, 'section' => 'Bebidas', 'ingredients' => ['Café', 'Leche'], 'allergens' => ['lactosa']],
                ['name' => 'Flat white', 'description' => 'Café doble con microespuma de leche.', 'price' => 3.10, 'calories' => 120, 'section' => 'Bebidas', 'ingredients' => ['Café', 'Leche'], 'allergens' => ['lactosa']],
                ['name' => 'Chai latte', 'description' => 'Infusión especiada con leche caliente.', 'price' => 3.40, 'calories' => 160, 'section' => 'Bebidas', 'ingredients' => ['Té chai', 'Leche', 'Canela', 'Cardamomo'], 'allergens' => ['lactosa']],
                ['name' => 'Zumo de naranja natural', 'description' => 'Zumo exprimido al momento.', 'price' => 3.50, 'calories' => 95, 'section' => 'Bebidas', 'ingredients' => ['Naranja'], 'allergens' => []],
                ['name' => 'Bagel de salmón y queso crema', 'description' => 'Bagel tostado con salmón ahumado y eneldo.', 'price' => 8.60, 'calories' => 520, 'section' => 'Principales', 'ingredients' => ['Bagel', 'Salmón ahumado', 'Queso crema', 'Eneldo'], 'allergens' => ['gluten', 'lactosa', 'pescado']],
                ['name' => 'Brownie de chocolate', 'description' => 'Brownie jugoso con nueces troceadas.', 'price' => 4.90, 'calories' => 410, 'section' => 'Postres', 'ingredients' => ['Chocolate', 'Harina', 'Huevo', 'Mantequilla', 'Nueces'], 'allergens' => ['gluten', 'huevos', 'lactosa', 'frutos_secos']],
            ],
            'espanola' => [
                ['name' => 'Croquetas de jamón ibérico', 'description' => 'Croquetas cremosas elaboradas con jamón ibérico.', 'price' => 9.80, 'calories' => 460, 'section' => 'Entrantes', 'ingredients' => ['Leche', 'Harina', 'Jamón ibérico', 'Huevo', 'Pan rallado'], 'allergens' => ['gluten', 'lactosa', 'huevos']],
                ['name' => 'Patatas bravas', 'description' => 'Patatas crujientes con salsa brava y alioli.', 'price' => 7.20, 'calories' => 390, 'section' => 'Entrantes', 'ingredients' => ['Patata', 'Tomate', 'Ajo', 'Aceite de oliva'], 'allergens' => ['huevos']],
                ['name' => 'Ensaladilla rusa', 'description' => 'Patata, zanahoria, atún y mayonesa casera.', 'price' => 7.50, 'calories' => 350, 'section' => 'Entrantes', 'ingredients' => ['Patata', 'Zanahoria', 'Atún', 'Mayonesa', 'Guisantes'], 'allergens' => ['huevos', 'pescado']],
                ['name' => 'Paella valenciana', 'description' => 'Arroz con pollo, conejo, judía verde y garrofón.', 'price' => 18.90, 'calories' => 780, 'section' => 'Principales', 'ingredients' => ['Arroz', 'Pollo', 'Conejo', 'Judía verde', 'Pimentón'], 'allergens' => []],
                ['name' => 'Bacalao al pil pil', 'description' => 'Lomo de bacalao confitado con salsa emulsionada.', 'price' => 19.80, 'calories' => 640, 'section' => 'Principales', 'ingredients' => ['Bacalao', 'Ajo', 'Aceite de oliva', 'Guindilla'], 'allergens' => ['pescado']],
                ['name' => 'Carrillera al vino tinto', 'description' => 'Carrillera de ternera estofada a fuego lento.', 'price' => 17.40, 'calories' => 720, 'section' => 'Principales', 'ingredients' => ['Carrillera de ternera', 'Vino tinto', 'Cebolla', 'Zanahoria'], 'allergens' => ['sulfitos']],
                ['name' => 'Tarta de Santiago', 'description' => 'Tarta tradicional de almendra y azúcar glas.', 'price' => 6.40, 'calories' => 360, 'section' => 'Postres', 'ingredients' => ['Almendra', 'Huevo', 'Azúcar', 'Ralladura de limón'], 'allergens' => ['huevos', 'frutos_secos']],
                ['name' => 'Arroz con leche', 'description' => 'Postre clásico aromatizado con canela.', 'price' => 5.20, 'calories' => 280, 'section' => 'Postres', 'ingredients' => ['Arroz', 'Leche', 'Canela', 'Azúcar'], 'allergens' => ['lactosa']],
                ['name' => 'Tinto de verano', 'description' => 'Vino tinto con limón y hielo.', 'price' => 3.90, 'calories' => 140, 'section' => 'Bebidas', 'ingredients' => ['Vino tinto', 'Refresco de limón'], 'allergens' => ['sulfitos']],
                ['name' => 'Agua con gas', 'description' => 'Agua mineral con gas.', 'price' => 2.50, 'calories' => 0, 'section' => 'Bebidas', 'ingredients' => ['Agua mineral'], 'allergens' => []],
                ['name' => 'Pulpo a la gallega', 'description' => 'Pulpo cocido con pimentón y aceite de oliva.', 'price' => 18.20, 'calories' => 420, 'section' => 'Principales', 'ingredients' => ['Pulpo', 'Patata', 'Pimentón', 'Aceite de oliva'], 'allergens' => ['moluscos']],
                ['name' => 'Gazpacho andaluz', 'description' => 'Sopa fría de tomate, pepino y pimiento.', 'price' => 6.90, 'calories' => 180, 'section' => 'Entrantes', 'ingredients' => ['Tomate', 'Pepino', 'Pimiento', 'Ajo', 'Pan'], 'allergens' => ['gluten']],
            ],
            'parrilla' => [
                ['name' => 'Provoleta a la parrilla', 'description' => 'Queso provolone fundido con orégano.', 'price' => 8.90, 'calories' => 420, 'section' => 'Entrantes', 'ingredients' => ['Provolone', 'Orégano', 'Tomate seco'], 'allergens' => ['lactosa']],
                ['name' => 'Chorizo criollo', 'description' => 'Chorizo criollo braseado al carbón.', 'price' => 7.50, 'calories' => 520, 'section' => 'Entrantes', 'ingredients' => ['Chorizo criollo', 'Pimienta', 'Sal'], 'allergens' => []],
                ['name' => 'Entraña a la brasa', 'description' => 'Entraña jugosa con chimichurri casero.', 'price' => 21.90, 'calories' => 760, 'section' => 'Principales', 'ingredients' => ['Entraña', 'Ajo', 'Perejil', 'Vinagre', 'Aceite'], 'allergens' => ['sulfitos']],
                ['name' => 'Costillar BBQ', 'description' => 'Costillar de cerdo lacado con salsa barbacoa.', 'price' => 19.60, 'calories' => 980, 'section' => 'Principales', 'ingredients' => ['Costilla de cerdo', 'Salsa BBQ', 'Miel', 'Mostaza'], 'allergens' => ['mostaza']],
                ['name' => 'Pollo de corral marinado', 'description' => 'Pollo marinado con limón y hierbas.', 'price' => 15.20, 'calories' => 650, 'section' => 'Principales', 'ingredients' => ['Pollo', 'Limón', 'Romero', 'Ajo'], 'allergens' => []],
                ['name' => 'Parrillada de verduras', 'description' => 'Pimientos, calabacín, berenjena y espárragos.', 'price' => 11.90, 'calories' => 320, 'section' => 'Principales', 'ingredients' => ['Pimiento', 'Calabacín', 'Berenjena', 'Espárragos'], 'allergens' => []],
                ['name' => 'Flan de huevo', 'description' => 'Flan casero con caramelo líquido.', 'price' => 5.40, 'calories' => 290, 'section' => 'Postres', 'ingredients' => ['Huevo', 'Leche', 'Azúcar'], 'allergens' => ['huevos', 'lactosa']],
                ['name' => 'Panqueque con dulce de leche', 'description' => 'Panqueque templado con dulce de leche.', 'price' => 6.30, 'calories' => 430, 'section' => 'Postres', 'ingredients' => ['Harina', 'Huevo', 'Leche', 'Dulce de leche'], 'allergens' => ['gluten', 'huevos', 'lactosa']],
                ['name' => 'Cerveza artesanal rubia', 'description' => 'Pinta de cerveza artesanal rubia.', 'price' => 4.30, 'calories' => 180, 'section' => 'Bebidas', 'ingredients' => ['Malta', 'Lúpulo', 'Agua'], 'allergens' => ['gluten']],
                ['name' => 'Limonada casera', 'description' => 'Limonada natural con menta fresca.', 'price' => 3.20, 'calories' => 95, 'section' => 'Bebidas', 'ingredients' => ['Limón', 'Menta', 'Azúcar', 'Agua'], 'allergens' => []],
                ['name' => 'Morcilla de Burgos', 'description' => 'Morcilla asada con pimiento confitado.', 'price' => 8.40, 'calories' => 560, 'section' => 'Entrantes', 'ingredients' => ['Morcilla', 'Arroz', 'Cebolla', 'Pimiento'], 'allergens' => []],
                ['name' => 'Solomillo de ternera', 'description' => 'Solomillo a la brasa con patatas panadera.', 'price' => 24.50, 'calories' => 790, 'section' => 'Principales', 'ingredients' => ['Solomillo', 'Patata', 'Romero', 'Aceite de oliva'], 'allergens' => []],
            ],
            'vegana' => [
                ['name' => 'Hummus con crudités', 'description' => 'Hummus de garbanzo con verduras frescas.', 'price' => 7.20, 'calories' => 300, 'section' => 'Entrantes', 'ingredients' => ['Garbanzo', 'Tahini', 'Ajo', 'Zanahoria', 'Pepino'], 'allergens' => ['sesamo']],
                ['name' => 'Guacamole con totopos', 'description' => 'Guacamole casero con pico de gallo.', 'price' => 8.10, 'calories' => 340, 'section' => 'Entrantes', 'ingredients' => ['Aguacate', 'Tomate', 'Cebolla', 'Lima', 'Maíz'], 'allergens' => []],
                ['name' => 'Bowl de quinoa mediterránea', 'description' => 'Quinoa con verduras asadas, espinaca y semillas.', 'price' => 11.50, 'calories' => 520, 'section' => 'Principales', 'ingredients' => ['Quinoa', 'Calabacín', 'Pimiento', 'Espinaca', 'Sésamo'], 'allergens' => ['sesamo']],
                ['name' => 'Curry de garbanzos y coco', 'description' => 'Curry suave de garbanzos con arroz jazmín.', 'price' => 12.40, 'calories' => 610, 'section' => 'Principales', 'ingredients' => ['Garbanzo', 'Leche de coco', 'Curry', 'Arroz jazmín', 'Cilantro'], 'allergens' => []],
                ['name' => 'Tacos de jackfruit', 'description' => 'Tacos veganos con jackfruit especiado.', 'price' => 10.80, 'calories' => 490, 'section' => 'Principales', 'ingredients' => ['Tortilla de maíz', 'Jackfruit', 'Cebolla roja', 'Lima', 'Cilantro'], 'allergens' => []],
                ['name' => 'Burger de lentejas', 'description' => 'Hamburguesa vegana de lentejas y remolacha.', 'price' => 11.90, 'calories' => 580, 'section' => 'Principales', 'ingredients' => ['Lentejas', 'Remolacha', 'Pan integral', 'Lechuga', 'Tomate'], 'allergens' => ['gluten']],
                ['name' => 'Brownie vegano', 'description' => 'Brownie de cacao con nueces, sin lácteos.', 'price' => 5.30, 'calories' => 360, 'section' => 'Postres', 'ingredients' => ['Cacao', 'Harina', 'Aceite de coco', 'Nueces', 'Azúcar moreno'], 'allergens' => ['gluten', 'frutos_secos']],
                ['name' => 'Tarta de zanahoria vegana', 'description' => 'Bizcocho de zanahoria con crema vegetal.', 'price' => 5.60, 'calories' => 380, 'section' => 'Postres', 'ingredients' => ['Zanahoria', 'Harina', 'Canela', 'Nueces', 'Bebida vegetal'], 'allergens' => ['gluten', 'frutos_secos', 'soja']],
                ['name' => 'Kombucha de jengibre', 'description' => 'Bebida fermentada natural y refrescante.', 'price' => 3.90, 'calories' => 40, 'section' => 'Bebidas', 'ingredients' => ['Té negro', 'Jengibre', 'Azúcar', 'Agua'], 'allergens' => []],
                ['name' => 'Lassi vegano de mango', 'description' => 'Batido vegetal de mango y cardamomo.', 'price' => 4.80, 'calories' => 190, 'section' => 'Bebidas', 'ingredients' => ['Mango', 'Bebida de soja', 'Cardamomo'], 'allergens' => ['soja']],
                ['name' => 'Ensalada de kale y naranja', 'description' => 'Kale masajeado con cítricos y almendra.', 'price' => 9.40, 'calories' => 290, 'section' => 'Entrantes', 'ingredients' => ['Kale', 'Naranja', 'Almendra', 'Aceite de oliva'], 'allergens' => ['frutos_secos']],
                ['name' => 'Gnocchi veganos de patata', 'description' => 'Gnocchi salteados con salsa de tomate y albahaca.', 'price' => 12.20, 'calories' => 610, 'section' => 'Principales', 'ingredients' => ['Patata', 'Harina', 'Tomate', 'Albahaca', 'Ajo'], 'allergens' => ['gluten']],
            ],
            'street' => [
                ['name' => 'Smash burger clásica', 'description' => 'Doble carne, cheddar, pepinillo y salsa especial.', 'price' => 9.90, 'calories' => 880, 'section' => 'Principales', 'ingredients' => ['Pan brioche', 'Carne de vacuno', 'Cheddar', 'Pepinillo', 'Salsa burger'], 'allergens' => ['gluten', 'lactosa', 'mostaza']],
                ['name' => 'Burger de pollo crispy', 'description' => 'Pollo rebozado, coleslaw y mayonesa de lima.', 'price' => 9.40, 'calories' => 790, 'section' => 'Principales', 'ingredients' => ['Pan', 'Pollo', 'Harina', 'Col', 'Mayonesa'], 'allergens' => ['gluten', 'huevos']],
                ['name' => 'Hot dog tex-mex', 'description' => 'Salchicha premium con pico de gallo y jalapeños.', 'price' => 7.80, 'calories' => 620, 'section' => 'Principales', 'ingredients' => ['Pan hot dog', 'Salchicha', 'Tomate', 'Jalapeño', 'Mostaza'], 'allergens' => ['gluten', 'mostaza']],
                ['name' => 'Patatas loaded bacon', 'description' => 'Patatas fritas con bacon, cheddar y cebolla crispy.', 'price' => 7.20, 'calories' => 690, 'section' => 'Entrantes', 'ingredients' => ['Patata', 'Bacon', 'Cheddar', 'Cebolla frita'], 'allergens' => ['gluten', 'lactosa']],
                ['name' => 'Tacos de ternera', 'description' => 'Tres tacos con ternera especiada y salsa chipotle.', 'price' => 8.90, 'calories' => 560, 'section' => 'Principales', 'ingredients' => ['Tortilla de maíz', 'Ternera', 'Cebolla', 'Cilantro', 'Chipotle'], 'allergens' => []],
                ['name' => 'Nachos completos', 'description' => 'Nachos con queso, guacamole, pico de gallo y crema agria.', 'price' => 8.50, 'calories' => 740, 'section' => 'Entrantes', 'ingredients' => ['Nachos de maíz', 'Queso', 'Guacamole', 'Tomate', 'Crema agria'], 'allergens' => ['lactosa']],
                ['name' => 'Donut glaseado', 'description' => 'Donut artesanal con glaseado de vainilla.', 'price' => 3.10, 'calories' => 330, 'section' => 'Postres', 'ingredients' => ['Harina', 'Leche', 'Huevo', 'Azúcar', 'Mantequilla'], 'allergens' => ['gluten', 'huevos', 'lactosa']],
                ['name' => 'Cookie de chocolate', 'description' => 'Galleta americana con chips de chocolate.', 'price' => 2.60, 'calories' => 250, 'section' => 'Postres', 'ingredients' => ['Harina', 'Chocolate', 'Mantequilla', 'Huevo'], 'allergens' => ['gluten', 'huevos', 'lactosa']],
                ['name' => 'Refresco cola', 'description' => 'Refresco de cola servido con hielo.', 'price' => 2.80, 'calories' => 140, 'section' => 'Bebidas', 'ingredients' => ['Agua carbonatada', 'Azúcar'], 'allergens' => []],
                ['name' => 'Cerveza lager', 'description' => 'Botella de cerveza lager fría.', 'price' => 3.20, 'calories' => 150, 'section' => 'Bebidas', 'ingredients' => ['Malta', 'Lúpulo', 'Agua'], 'allergens' => ['gluten']],
                ['name' => 'Sándwich pastrami', 'description' => 'Pastrami ahumado con pepinillos y mostaza antigua.', 'price' => 9.10, 'calories' => 680, 'section' => 'Principales', 'ingredients' => ['Pan', 'Pastrami', 'Pepinillo', 'Mostaza'], 'allergens' => ['gluten', 'mostaza']],
                ['name' => 'Aros de cebolla', 'description' => 'Aros de cebolla rebozados y crujientes.', 'price' => 5.90, 'calories' => 420, 'section' => 'Entrantes', 'ingredients' => ['Cebolla', 'Harina', 'Huevo', 'Pan rallado'], 'allergens' => ['gluten', 'huevos']],
            ],
        ];

        return $catalog[$cuisine] ?? $catalog['espanola'];
    }

    private function mapBusinessType(string $type): string
    {
        return match ($type) {
            'cafe', 'coffee', 'bakery'        => 'cafe',
            'bar', 'pub', 'cocktail'          => 'bar',
            'fastfood', 'fast_food', 'street' => 'fastfood',
            'finedining', 'fine_dining'       => 'finedining',
            default                           => 'restaurant',
        };
    }

    private function tenantProfiles(): array
    {
        return [
            ['name' => 'Casa Brisa Mediterranea', 'short_name' => 'Brisa', 'type' => 'Restaurante', 'cuisine' => 'espanola', 'city' => 'Valencia', 'province' => 'Valencia', 'country' => 'ES', 'address' => 'Calle de la Marina 12', 'description' => 'Cocina mediterránea de mercado con producto local.'],
            ['name' => 'Pizzeria Forno Rosso', 'short_name' => 'Forno', 'type' => 'Pizzería', 'cuisine' => 'italiana', 'city' => 'Madrid', 'province' => 'Madrid', 'country' => 'ES', 'address' => 'Calle Mayor 38', 'description' => 'Pizzas napolitanas de larga fermentación y horno de piedra.'],
            ['name' => 'Cafe Nube de Canela', 'short_name' => 'Nube', 'type' => 'Cafetería', 'cuisine' => 'cafeteria', 'city' => 'Sevilla', 'province' => 'Sevilla', 'country' => 'ES', 'address' => 'Plaza del Museo 5', 'description' => 'Especialidad en café de origen y brunch artesanal.'],
            ['name' => 'Parrilla El Carbon Vivo', 'short_name' => 'Carbon', 'type' => 'Parrilla', 'cuisine' => 'parrilla', 'city' => 'Bilbao', 'province' => 'Bizkaia', 'country' => 'ES', 'address' => 'Calle Ledesma 19', 'description' => 'Carnes premium a la brasa y guarniciones de temporada.'],
            ['name' => 'Verde Raiz Bistro', 'short_name' => 'Raiz', 'type' => 'Restaurante vegano', 'cuisine' => 'vegana', 'city' => 'Barcelona', 'province' => 'Barcelona', 'country' => 'ES', 'address' => 'Carrer del Parlament 21', 'description' => 'Cocina vegetal creativa, saludable y sabrosa.'],
            ['name' => 'La Ruta Street Kitchen', 'short_name' => 'Ruta', 'type' => 'Food truck', 'cuisine' => 'street', 'city' => 'Malaga', 'province' => 'Malaga', 'country' => 'ES', 'address' => 'Avenida de Andalucia 88', 'description' => 'Street food internacional con recetas contundentes.'],
            ['name' => 'Heladeria Olas de Leche', 'short_name' => 'Olas', 'type' => 'Heladería', 'cuisine' => 'cafeteria', 'city' => 'Alicante', 'province' => 'Alicante', 'country' => 'ES', 'address' => 'Paseo Maritimo 14', 'description' => 'Helados artesanos, granizados y copas especiales.'],
            ['name' => 'Dulce Trigo Obrador', 'short_name' => 'Trigo', 'type' => 'Pastelería', 'cuisine' => 'cafeteria', 'city' => 'Zaragoza', 'province' => 'Zaragoza', 'country' => 'ES', 'address' => 'Calle Alfonso I 9', 'description' => 'Pastelería fina y bollería de obrador propio.'],
            ['name' => 'Taberna Puerto Azul', 'short_name' => 'Azul', 'type' => 'Taberna', 'cuisine' => 'espanola', 'city' => 'Cadiz', 'province' => 'Cadiz', 'country' => 'ES', 'address' => 'Calle San Francisco 27', 'description' => 'Tapas clásicas, vinos andaluces y ambiente local.'],
            ['name' => 'Gastrobar Lumbre', 'short_name' => 'Lumbre', 'type' => 'Gastrobar', 'cuisine' => 'espanola', 'city' => 'Valladolid', 'province' => 'Valladolid', 'country' => 'ES', 'address' => 'Plaza Mayor 3', 'description' => 'Platos para compartir con toques contemporáneos.'],
            ['name' => 'Casa del Arroz Norte', 'short_name' => 'ArrozN', 'type' => 'Restaurante', 'cuisine' => 'espanola', 'city' => 'Santander', 'province' => 'Cantabria', 'country' => 'ES', 'address' => 'Calle Hernan Cortes 11', 'description' => 'Arroces melosos y marineros preparados al momento.'],
            ['name' => 'Pizzeria Piazza Nova', 'short_name' => 'Piazza', 'type' => 'Pizzería', 'cuisine' => 'italiana', 'city' => 'Granada', 'province' => 'Granada', 'country' => 'ES', 'address' => 'Calle Recogidas 44', 'description' => 'Especialidad en pizza romana y pasta fresca.'],
            ['name' => 'Cafe Molino Blanco', 'short_name' => 'Molino', 'type' => 'Cafetería', 'cuisine' => 'cafeteria', 'city' => 'Oviedo', 'province' => 'Asturias', 'country' => 'ES', 'address' => 'Calle Uria 17', 'description' => 'Café de especialidad y desayunos de temporada.'],
            ['name' => 'Asador Encina Real', 'short_name' => 'Encina', 'type' => 'Asador', 'cuisine' => 'parrilla', 'city' => 'Burgos', 'province' => 'Burgos', 'country' => 'ES', 'address' => 'Calle Vitoria 66', 'description' => 'Asados tradicionales y brasas de encina.'],
            ['name' => 'Vegan Green Atelier', 'short_name' => 'Atelier', 'type' => 'Restaurante vegano', 'cuisine' => 'vegana', 'city' => 'Palma', 'province' => 'Islas Baleares', 'country' => 'ES', 'address' => 'Carrer de Jaume III 25', 'description' => 'Propuesta vegetal de autor con ingredientes frescos.'],
            ['name' => 'Truck del Barrio', 'short_name' => 'Barrio', 'type' => 'Food truck', 'cuisine' => 'street', 'city' => 'Murcia', 'province' => 'Murcia', 'country' => 'ES', 'address' => 'Avenida Libertad 53', 'description' => 'Burgers, tacos y bocados urbanos para llevar.'],
            ['name' => 'Gelato Porto Dolce', 'short_name' => 'Porto', 'type' => 'Heladería', 'cuisine' => 'cafeteria', 'city' => 'A Coruna', 'province' => 'A Coruna', 'country' => 'ES', 'address' => 'Rua Real 40', 'description' => 'Gelato italiano con fruta natural y toppings artesanos.'],
            ['name' => 'Horno Santa Clara', 'short_name' => 'Clara', 'type' => 'Pastelería', 'cuisine' => 'cafeteria', 'city' => 'Cordoba', 'province' => 'Cordoba', 'country' => 'ES', 'address' => 'Calle Cruz Conde 30', 'description' => 'Pastelería tradicional con recetas familiares.'],
            ['name' => 'Bodega La Cepa Viva', 'short_name' => 'Cepa', 'type' => 'Bodega', 'cuisine' => 'espanola', 'city' => 'Logrono', 'province' => 'La Rioja', 'country' => 'ES', 'address' => 'Calle Laurel 18', 'description' => 'Pinchos riojanos y amplia carta de vinos.'],
            ['name' => 'Cerveceria Norte 21', 'short_name' => 'N21', 'type' => 'Cervecería', 'cuisine' => 'street', 'city' => 'Gijon', 'province' => 'Asturias', 'country' => 'ES', 'address' => 'Calle Corrida 90', 'description' => 'Cervezas artesanas y cocina informal.'],
            ['name' => 'Casa Sabor de Plaza', 'short_name' => 'Plaza', 'type' => 'Restaurante', 'cuisine' => 'espanola', 'city' => 'Toledo', 'province' => 'Toledo', 'country' => 'ES', 'address' => 'Calle Comercio 12', 'description' => 'Recetas castellanas con toque actual.'],
            ['name' => 'Pizzeria Vesubio 88', 'short_name' => 'Vesubio', 'type' => 'Pizzería', 'cuisine' => 'italiana', 'city' => 'Salamanca', 'province' => 'Salamanca', 'country' => 'ES', 'address' => 'Plaza de Espana 8', 'description' => 'Masa madre, ingredientes italianos y horno a leña.'],
            ['name' => 'Cafe Trigo y Moka', 'short_name' => 'Moka', 'type' => 'Cafetería', 'cuisine' => 'cafeteria', 'city' => 'Leon', 'province' => 'Leon', 'country' => 'ES', 'address' => 'Calle Ancha 24', 'description' => 'Brunch casero, café filtrado y repostería propia.'],
            ['name' => 'Parrilla Brava del Sur', 'short_name' => 'Brava', 'type' => 'Parrilla', 'cuisine' => 'parrilla', 'city' => 'Jerez de la Frontera', 'province' => 'Cadiz', 'country' => 'ES', 'address' => 'Avenida Alvaro Domecq 41', 'description' => 'Brasa tradicional, carnes y verduras al punto.'],
            ['name' => 'Raices Plant Kitchen', 'short_name' => 'Plant', 'type' => 'Restaurante vegano', 'cuisine' => 'vegana', 'city' => 'Pamplona', 'province' => 'Navarra', 'country' => 'ES', 'address' => 'Calle Estafeta 35', 'description' => 'Plant based sin procesados y de temporada.'],
            ['name' => 'Food Wheels Iberia', 'short_name' => 'Wheels', 'type' => 'Food truck', 'cuisine' => 'street', 'city' => 'Almeria', 'province' => 'Almeria', 'country' => 'ES', 'address' => 'Paseo de Almeria 77', 'description' => 'Comida callejera inspirada en distintos países.'],
            ['name' => 'Helados Nevada 14', 'short_name' => 'Nevada', 'type' => 'Heladería', 'cuisine' => 'cafeteria', 'city' => 'San Sebastian', 'province' => 'Gipuzkoa', 'country' => 'ES', 'address' => 'Calle Mayor 2', 'description' => 'Helados cremosos y sorbetes de fruta fresca.'],
            ['name' => 'Dulce Alba Obrador', 'short_name' => 'Alba', 'type' => 'Pastelería', 'cuisine' => 'cafeteria', 'city' => 'Albacete', 'province' => 'Albacete', 'country' => 'ES', 'address' => 'Calle Tesifonte Gallego 6', 'description' => 'Bollería diaria, tartas personalizadas y cafés.'],
            ['name' => 'Taberna Don Ramon', 'short_name' => 'Ramon', 'type' => 'Taberna', 'cuisine' => 'espanola', 'city' => 'Jaen', 'province' => 'Jaen', 'country' => 'ES', 'address' => 'Calle Bernabe Soriano 15', 'description' => 'Taberna de tapeo clásico y cocina de cuchara.'],
            ['name' => 'Gastrobar Mar y Brasa', 'short_name' => 'MBrasa', 'type' => 'Gastrobar', 'cuisine' => 'espanola', 'city' => 'Huelva', 'province' => 'Huelva', 'country' => 'ES', 'address' => 'Avenida Martin Alonso Pinzon 10', 'description' => 'Pescado, brasa y carta moderna de temporada.'],
            ['name' => 'Casa del Puerto Viejo', 'short_name' => 'Puerto', 'type' => 'Restaurante', 'cuisine' => 'espanola', 'city' => 'Vigo', 'province' => 'Pontevedra', 'country' => 'ES', 'address' => 'Rua Colon 7', 'description' => 'Recetas gallegas y producto del mar.'],
            ['name' => 'Pizzeria Bella Crosta', 'short_name' => 'Crosta', 'type' => 'Pizzería', 'cuisine' => 'italiana', 'city' => 'Tarragona', 'province' => 'Tarragona', 'country' => 'ES', 'address' => 'Rambla Nova 62', 'description' => 'Pizzas con harina italiana y mozzarella fresca.'],
            ['name' => 'Cafe Ronda 21', 'short_name' => 'Ronda', 'type' => 'Cafetería', 'cuisine' => 'cafeteria', 'city' => 'Girona', 'province' => 'Girona', 'country' => 'ES', 'address' => 'Carrer de la Barca 14', 'description' => 'Desayunos completos y repostería artesanal.'],
            ['name' => 'Asador Leña Fina', 'short_name' => 'Lena', 'type' => 'Asador', 'cuisine' => 'parrilla', 'city' => 'Segovia', 'province' => 'Segovia', 'country' => 'ES', 'address' => 'Calle Real 44', 'description' => 'Asados de horno y carnes seleccionadas.'],
            ['name' => 'Verde y Sal Marina', 'short_name' => 'VSal', 'type' => 'Restaurante vegano', 'cuisine' => 'vegana', 'city' => 'Lugo', 'province' => 'Lugo', 'country' => 'ES', 'address' => 'Rua da Raiña 11', 'description' => 'Cocina vegetal con influencia atlántica.'],
            ['name' => 'Road Bite Truck', 'short_name' => 'Road', 'type' => 'Food truck', 'cuisine' => 'street', 'city' => 'Badajoz', 'province' => 'Badajoz', 'country' => 'ES', 'address' => 'Avenida de Europa 31', 'description' => 'Bocados urbanos y propuestas rápidas de calidad.'],
            ['name' => 'Heladeria Sol y Nata', 'short_name' => 'SolN', 'type' => 'Heladería', 'cuisine' => 'cafeteria', 'city' => 'Benidorm', 'province' => 'Alicante', 'country' => 'ES', 'address' => 'Avenida del Mediterraneo 19', 'description' => 'Copas heladas, gofres y meriendas dulces.'],
            ['name' => 'Obrador Dulce Via', 'short_name' => 'Via', 'type' => 'Pastelería', 'cuisine' => 'cafeteria', 'city' => 'Cuenca', 'province' => 'Cuenca', 'country' => 'ES', 'address' => 'Calle Carreteria 27', 'description' => 'Repostería fina con ingredientes naturales.'],
            ['name' => 'Bodega La Cepa Antigua', 'short_name' => 'Antigua', 'type' => 'Bodega', 'cuisine' => 'espanola', 'city' => 'Ourense', 'province' => 'Ourense', 'country' => 'ES', 'address' => 'Rua do Paseo 52', 'description' => 'Cocina de mercado y selección de vinos de autor.'],
            ['name' => 'Cerveceria Malta Norte', 'short_name' => 'Malta', 'type' => 'Cervecería', 'cuisine' => 'street', 'city' => 'Vitoria', 'province' => 'Araba', 'country' => 'ES', 'address' => 'Calle Dato 9', 'description' => 'Tapas, burgers y cervezas artesanas de tirador.'],
            ['name' => 'Casa del Prado Alto', 'short_name' => 'Prado', 'type' => 'Restaurante', 'cuisine' => 'espanola', 'city' => 'Avila', 'province' => 'Avila', 'country' => 'ES', 'address' => 'Calle Estrada 5', 'description' => 'Cocina castellana y horno tradicional.'],
            ['name' => 'Pizzeria Cornicione', 'short_name' => 'Corni', 'type' => 'Pizzería', 'cuisine' => 'italiana', 'city' => 'Cartagena', 'province' => 'Murcia', 'country' => 'ES', 'address' => 'Calle Mayor 60', 'description' => 'Especialistas en pizza napolitana de borde alto.'],
            ['name' => 'Cafe Bosque y Latte', 'short_name' => 'Bosque', 'type' => 'Cafetería', 'cuisine' => 'cafeteria', 'city' => 'Soria', 'province' => 'Soria', 'country' => 'ES', 'address' => 'Calle Collado 13', 'description' => 'Café de especialidad y carta brunch todo el día.'],
            ['name' => 'Parrilla Fuego de Roble', 'short_name' => 'Roble', 'type' => 'Parrilla', 'cuisine' => 'parrilla', 'city' => 'Lleida', 'province' => 'Lleida', 'country' => 'ES', 'address' => 'Avinguda Blondel 18', 'description' => 'Cortes premium y brasa de carbón vegetal.'],
            ['name' => 'Verde Huerta Lab', 'short_name' => 'Huerta', 'type' => 'Restaurante vegano', 'cuisine' => 'vegana', 'city' => 'Teruel', 'province' => 'Teruel', 'country' => 'ES', 'address' => 'Plaza del Torico 4', 'description' => 'Menú vegetal con técnicas contemporáneas.'],
            ['name' => 'Urban Wheels Food', 'short_name' => 'Urban', 'type' => 'Food truck', 'cuisine' => 'street', 'city' => 'Pontevedra', 'province' => 'Pontevedra', 'country' => 'ES', 'address' => 'Rua Michelena 21', 'description' => 'Street food con sabor internacional y rápido servicio.'],
            ['name' => 'Helados Costa Clara', 'short_name' => 'Costa', 'type' => 'Heladería', 'cuisine' => 'cafeteria', 'city' => 'Castellon', 'province' => 'Castellon', 'country' => 'ES', 'address' => 'Avenida Rey Don Jaime 36', 'description' => 'Helados italianos con obrador propio.'],
            ['name' => 'Pasteleria La Espiga', 'short_name' => 'Espiga', 'type' => 'Pastelería', 'cuisine' => 'cafeteria', 'city' => 'Huesca', 'province' => 'Huesca', 'country' => 'ES', 'address' => 'Calle Coso Alto 22', 'description' => 'Tartas artesanas y bollería de mantequilla.'],
            ['name' => 'Taberna del Mercado', 'short_name' => 'Mercado', 'type' => 'Taberna', 'cuisine' => 'espanola', 'city' => 'Merida', 'province' => 'Badajoz', 'country' => 'ES', 'address' => 'Calle Santa Eulalia 49', 'description' => 'Tapeo tradicional y platos para compartir.'],
            ['name' => 'Gastrobar Finca 29', 'short_name' => 'Finca', 'type' => 'Gastrobar', 'cuisine' => 'espanola', 'city' => 'Caceres', 'province' => 'Caceres', 'country' => 'ES', 'address' => 'Plaza Mayor 29', 'description' => 'Cocina de producto con guiños de autor.'],
            ['name' => 'Casa Ruta de la Sal', 'short_name' => 'Sal', 'type' => 'Restaurante', 'cuisine' => 'espanola', 'city' => 'Ibiza', 'province' => 'Islas Baleares', 'country' => 'ES', 'address' => 'Avenida Santa Eulalia 7', 'description' => 'Cocina mediterránea con enfoque costero.'],
            ['name' => 'Pizzeria Napoli Centro', 'short_name' => 'Napoli', 'type' => 'Pizzería', 'cuisine' => 'italiana', 'city' => 'Reus', 'province' => 'Tarragona', 'country' => 'ES', 'address' => 'Carrer Monterols 14', 'description' => 'Pizzas clásicas, horno de piedra y antipasti italianos.'],
            ['name' => 'Cafe Central 45', 'short_name' => 'Central', 'type' => 'Cafetería', 'cuisine' => 'cafeteria', 'city' => 'Ferrol', 'province' => 'A Coruna', 'country' => 'ES', 'address' => 'Calle Real 45', 'description' => 'Cafetería acogedora con desayunos completos.'],
            ['name' => 'Parrilla Sierra Viva', 'short_name' => 'Sierra', 'type' => 'Parrilla', 'cuisine' => 'parrilla', 'city' => 'Ronda', 'province' => 'Malaga', 'country' => 'ES', 'address' => 'Calle Espinel 28', 'description' => 'Brasa lenta, carnes maduradas y verduras de huerto.'],
            ['name' => 'Verde Aurora Kitchen', 'short_name' => 'Aurora', 'type' => 'Restaurante vegano', 'cuisine' => 'vegana', 'city' => 'Mahon', 'province' => 'Islas Baleares', 'country' => 'ES', 'address' => 'Carrer Nou 16', 'description' => 'Platos plant-based con técnica y sabor.'],
            ['name' => 'Wagon Street Foods', 'short_name' => 'Wagon', 'type' => 'Food truck', 'cuisine' => 'street', 'city' => 'Santiago de Compostela', 'province' => 'A Coruna', 'country' => 'ES', 'address' => 'Rua do Franco 33', 'description' => 'Street food variado para eventos y zonas urbanas.'],
            ['name' => 'Heladeria Copo de Nieve', 'short_name' => 'Copo', 'type' => 'Heladería', 'cuisine' => 'cafeteria', 'city' => 'Manresa', 'province' => 'Barcelona', 'country' => 'ES', 'address' => 'Passeig de Pere III 20', 'description' => 'Helados artesanos y postres fríos creativos.'],
            ['name' => 'Obrador Miga Dorada', 'short_name' => 'Miga', 'type' => 'Pastelería', 'cuisine' => 'cafeteria', 'city' => 'Elche', 'province' => 'Alicante', 'country' => 'ES', 'address' => 'Calle Corredora 54', 'description' => 'Panadería y repostería de fermentaciones lentas.'],
        ];
    }
}
