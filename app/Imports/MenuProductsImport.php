<?php

namespace App\Imports;

use App\Models\Allergen;
use App\Models\Product;
use App\Models\Section;
use App\Services\IngredientCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MenuProductsImport implements ToCollection, WithHeadingRow
{
    private int $importedCount = 0;

    public function __construct(
        private readonly int $menuId,
        private readonly string $tenantId,
    ) {}

    public function collection(Collection $rows): void
    {
        /** @var array<string, int> $allergenMap */
        $allergenMap = Allergen::where('tenant_id', $this->tenantId)
            ->pluck('id', 'code')
            ->toArray();

        /** @var array<string, Section> $sectionCache */
        $sectionCache = [];

        $ingredientCatalog = app(IngredientCatalog::class);

        $maxSortOrder = DB::table('menu_product')
            ->where('menu_id', $this->menuId)
            ->max('sort_order') ?? 0;

        DB::transaction(function () use ($rows, $allergenMap, &$sectionCache, $ingredientCatalog, &$maxSortOrder): void {
            foreach ($rows as $row) {
                $sectionName = trim((string) ($row['seccion'] ?? $row['section'] ?? ''));
                $productName = trim((string) ($row['producto'] ?? $row['product'] ?? ''));

                if ($sectionName === '' || $productName === '') {
                    continue;
                }

                // Get or create section
                if (! isset($sectionCache[$sectionName])) {
                    $sectionCache[$sectionName] = Section::firstOrCreate(
                        ['menu_id' => $this->menuId, 'name' => $sectionName],
                        ['tenant_id' => $this->tenantId],
                    );
                }
                $section = $sectionCache[$sectionName];

                // Create product
                $rawPrice = $row['precio'] ?? $row['price'] ?? 0;
                $rawCalories = $row['calorias'] ?? $row['calories'] ?? null;

                $product = Product::create([
                    'name' => $productName,
                    'description' => trim((string) ($row['descripcion'] ?? $row['description'] ?? '')) ?: null,
                    'price' => is_numeric($rawPrice) ? (float) $rawPrice : 0.0,
                    'calories' => is_numeric($rawCalories) ? (int) $rawCalories : null,
                    'tenant_id' => $this->tenantId,
                ]);

                // Attach to section via pivot
                DB::table('product_section')->insert([
                    'product_id' => $product->id,
                    'section_id' => $section->id,
                    'tenant_id' => $this->tenantId,
                ]);

                // Attach to menu via pivot
                $maxSortOrder++;
                DB::table('menu_product')->insert([
                    'menu_id' => $this->menuId,
                    'product_id' => $product->id,
                    'tenant_id' => $this->tenantId,
                    'sort_order' => $maxSortOrder,
                ]);

                // Process allergens
                $allergenStr = trim((string) ($row['alergenos'] ?? $row['allergens'] ?? ''));
                if ($allergenStr !== '') {
                    $allergenRows = [];
                    foreach (array_map('trim', explode(',', $allergenStr)) as $code) {
                        $code = strtolower($code);
                        if (isset($allergenMap[$code])) {
                            $allergenRows[] = [
                                'allergen_id' => $allergenMap[$code],
                                'product_id' => $product->id,
                                'tenant_id' => $this->tenantId,
                            ];
                        }
                    }
                    if (! empty($allergenRows)) {
                        DB::table('allergen_product')->insert($allergenRows);
                    }
                }

                // Process ingredients
                $ingredientStr = trim((string) ($row['ingredientes'] ?? $row['ingredients'] ?? ''));
                if ($ingredientStr !== '') {
                    foreach (array_map('trim', explode(',', $ingredientStr)) as $name) {
                        if ($name === '') {
                            continue;
                        }
                        $ingredient = $ingredientCatalog->findOrImport($name);
                        DB::table('ingredient_product')->insertOrIgnore([
                            'ingredient_id' => $ingredient->id,
                            'product_id' => $product->id,
                            'tenant_id' => $this->tenantId,
                        ]);
                    }
                }

                $this->importedCount++;
            }
        });
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
