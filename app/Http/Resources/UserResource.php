<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot ?? null;

        $permissions = $pivot?->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }
        $permissions = is_array($permissions) && ! empty($permissions)
            ? $permissions
            : ['scope' => 'all'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            'tenant_role' => $pivot?->role,
            'tenant_permissions' => $permissions,
            'joined_at' => $pivot?->joined_at,
        ];
    }
}
