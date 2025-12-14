<?php

// File: database/seeders/TenantSeeder.php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear un Usuario Dueño (si no existe ya por el UserSeeder)
        $user = User::firstOrCreate(
            ['email' => 'mario@menuflow.app'],
            [
                'name' => 'Mario Dueño',
                'last_name' => 'Perez',
                'password' => Hash::make('password'),
            ]
        );

        $role = Role::where('name', 'Owner')->first();
        if ($role) {
            $user->assignRole($role);
            $user->save();
        }

        // 2. Datos del tenant
        $tenantId = 'pizzeria-mario';
        $tenantAttributes = [
            'stripe_id' => 'cus_123456789',
            'pm_type' => 'card',
            'pm_last_four' => '4242',
            'trial_ends_at' => now()->addDays(14),
            'data' => json_encode(new \stdClass),
            'stripe_connect_id' => 'acct_123456789',
            'updated_at' => now(),
            'created_at' => now(),
        ];

        // 3. Intentar obtener/crear el tenant usando el flujo normal
        $tenant = Tenant::find($tenantId);

        if ($tenant) {
            $tenant->fill($tenantAttributes);
            $tenant->save();
        } else {
            try {
                // Intento normal: puede lanzar excepción si el sistema intenta crear una DB ya existente.
                $tenant = Tenant::create(array_merge(['id' => $tenantId], $tenantAttributes));
            } catch (\Throwable $e) {
                // Fallback: si la creación falla (por ejemplo: "Database ... already exists"),
                // insertar o actualizar directamente en la tabla `tenants` para evitar la lógica que crea DBs.
                DB::table('tenants')->updateOrInsert(
                    ['id' => $tenantId],
                    $tenantAttributes
                );

                // Intentar obtener el modelo ahora (si la app tiene modelos cargados)
                $tenant = Tenant::find($tenantId);
            }
        }

        // 4. Crear Dominio (Opcional). Si tenemos el modelo, usar la relación; si no, usar DB directo.
        $domainName = 'pizzeria-mario.localhost';

        if ($tenant instanceof Tenant) {
            $tenant->domains()->firstOrCreate(['domain' => $domainName]);
        } else {
            // Fallback directo a la tabla domains (evita triggers/bootstrappers)
            DB::table('domains')->updateOrInsert(
                ['domain' => $domainName],
                [
                    'tenant_id' => $tenantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
