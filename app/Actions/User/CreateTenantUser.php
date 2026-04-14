<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Create a user and attach them to the current tenant with a specific role
 * and scope. Used from the tenant admin panel (/panel/users).
 *
 * Roles:
 *   - owner   → full access to the tenant (can manage users, billing, etc.)
 *   - editor  → limited to menus/products/ingredients, scoped by location
 *
 * Scope (only relevant for editors):
 *   - ['scope' => 'all']
 *   - ['scope' => 'locations', 'location_ids' => [1, 2]]
 */
class CreateTenantUser
{
    public function execute(array $data): User
    {
        /** @var User $user */
        $user = User::create([
            'name' => $data['name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        tenant()->users()->attach($user->id, [
            'role' => $data['role'],
            'permissions' => json_encode($this->buildPermissions($data)),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPermissions(array $data): array
    {
        $role = $data['role'];

        if ($role === 'owner') {
            return ['scope' => 'all'];
        }

        $scope = $data['location_scope'] ?? 'all';

        if ($scope === 'all') {
            return ['scope' => 'all'];
        }

        return [
            'scope' => 'locations',
            'location_ids' => array_values(array_map('intval', $data['location_ids'] ?? [])),
        ];
    }
}
