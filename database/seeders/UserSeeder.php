<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Owner',
        ];
        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role]);
        }
        $roleAdmin = Role::where('name', 'Admin')->first();
        $admins = [
            [
                'name' => 'Admin',
                'last_name' => 'FlowSuite',
                'email' => 'admin@flowsuite.com',
                'password' => bcrypt('password'),
            ],
        ];
        foreach ($admins as $admin) {
            $user = User::updateOrCreate(
                ['email' => $admin['email']],
                $admin,
            );
            if (! $user->hasRole('Admin')) {
                $user->assignRole($roleAdmin);
            }
        }
    }
}
