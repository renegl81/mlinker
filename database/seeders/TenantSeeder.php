<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
                'password' => Hash::make('password'),
            ]
        );

        // 2. Crear el Tenant (Restaurante)
        // Usamos firstOrCreate para evitar duplicados si corres el seeder varias veces
        $tenant = Tenant::firstOrCreate(
            ['id' => 'pizzeria-mario'], // ID personalizado (útil para URLs: pizzeria-mario.menuflow.app)
            [
                // Aquí irían otros campos de tu tabla tenants si los tienes (plan, etc.)
                // 'plan' => 'pro',
            ]
        );

        // 3. Vincular Usuario con Tenant (Tabla Pivote)
        // Verificamos si ya está vinculado para no duplicar
        if (! $tenant->users()->where('user_id', $user->id)->exists()) {
            $tenant->users()->attach($user->id, ['role' => 'owner']);
        }

        // 4. Crear Dominio (Opcional, si usas dominios/subdominios con stancl)
        $tenant->domains()->firstOrCreate([
            'domain' => 'pizzeria-mario.localhost' // O el dominio que uses en local
        ]);
    }
}
