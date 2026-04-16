<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateTenantUser
{
    public function execute(User $user, array $data): User
    {
        $userFields = [
            'name' => $data['name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $userFields['password'] = Hash::make($data['password']);
        }

        $user->update($userFields);

        // Update tenant pivot (role + scope). Only if the current user is the
        // one being edited (self-edit of profile), skip the role change.
        $pivotData = [
            'role' => $data['role'],
            'permissions' => json_encode($this->buildPermissions($data)),
        ];

        tenant()->users()->updateExistingPivot($user->id, $pivotData);

        return $user->fresh();
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
