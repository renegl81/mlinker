<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\User\CreateTenantUser;
use App\Actions\User\UpdateTenantUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantUserRequest;
use App\Http\Requests\Tenant\UpdateTenantUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Location;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = tenant()->users();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'ilike', "%{$search}%")
                    ->orWhere('users.last_name', 'ilike', "%{$search}%")
                    ->orWhere('users.email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->wherePivot('role', $request->input('role'));
        }

        $users = $query->paginate(10)->withQueryString();

        return Inertia::render('admin/tenant/users/Index', [
            'users' => UserResource::collection($users),
            'filters' => $request->only(['search', 'role']),
            'hasTeam' => $this->tenantHasTeam(),
            'currentUserRole' => $request->user()?->currentTenantRole(),
        ]);
    }

    public function create()
    {
        $this->ensureCanManageUsers();

        return Inertia::render('admin/tenant/users/Create', [
            'locations' => Location::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreTenantUserRequest $request, CreateTenantUser $createTenantUser): RedirectResponse
    {
        $this->ensureCanManageUsers();

        $createTenantUser->execute($request->validated());

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user, Request $request)
    {
        $this->ensureCanManageUsers();

        abort_unless(
            tenant()->users()->where('users.id', $user->id)->exists(),
            404,
        );

        $pivot = tenant()->users()->where('users.id', $user->id)->first()?->pivot;
        $permissions = $pivot?->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?: [];
        }
        $permissions = is_array($permissions) && ! empty($permissions)
            ? $permissions
            : ['scope' => 'all'];

        return Inertia::render('admin/tenant/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'tenant_role' => $pivot?->role,
                'tenant_permissions' => $permissions,
            ],
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'currentUserId' => $request->user()?->id,
        ]);
    }

    public function update(UpdateTenantUserRequest $request, User $user, UpdateTenantUser $updateTenantUser): RedirectResponse
    {
        $this->ensureCanManageUsers();

        abort_unless(
            tenant()->users()->where('users.id', $user->id)->exists(),
            404,
        );

        $updateTenantUser->execute($user, $request->validated());

        return redirect()->route('tenant.users.edit', ['user' => $user])
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanManageUsers();

        abort_unless(
            tenant()->users()->where('users.id', $user->id)->exists(),
            404,
        );

        if ($user->id === $request->user()?->id) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $pivotRole = tenant()->users()->where('users.id', $user->id)->first()?->pivot->role;

        if ($pivotRole === 'owner') {
            $ownerCount = tenant()->users()->wherePivot('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return back()->with('error', 'No puedes eliminar al último owner.');
            }
        }

        tenant()->users()->detach($user->id);

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuario eliminado del tenant.');
    }

    private function ensureCanManageUsers(): void
    {
        $user = request()->user();
        abort_unless($user?->isCurrentTenantOwner(), 403, 'Solo los owners pueden gestionar usuarios.');
        abort_unless($this->tenantHasTeam(), 403, 'La gestión de equipo requiere un plan Pro o superior.');
    }

    private function tenantHasTeam(): bool
    {
        $tenantId = tenant()->id;

        $subscription = Subscription::where('tenant_id', $tenantId)
            ->latest()
            ->with('plan')
            ->first();

        $plan = $subscription?->plan ?? Plan::free();

        return (bool) ($plan?->has_team ?? false);
    }
}
