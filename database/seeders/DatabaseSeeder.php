<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Tenant;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedById;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * @throws TenantCouldNotBeIdentifiedById
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CountrySeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            TenantSeeder::class,
        ]);

        $mainTenant = Tenant::find('pizzeria-mario');

        if ($mainTenant) {
            // A. INICIALIZAR EL CONTEXTO
            // Esto hace que todos los modelos siguientes inyecten automáticamente
            // el 'tenant_id' => 'pizzeria-mario'
            tenancy()->initialize($mainTenant);

            $this->command->info("🌱 Sembrando datos para el restaurante: " . $mainTenant->id);

            // B. EJECUTAR SEEDERS DE CONTENIDO
            // Nota: Estos seeders NO necesitan saber del tenant explícitamente,
            // el Trait 'BelongsToTenant' en los modelos hará la magia.
            $this->call([
                AllergenSeeder::class,

            ]);

            // C. FINALIZAR CONTEXTO
            tenancy()->end();
        }
    }
}
