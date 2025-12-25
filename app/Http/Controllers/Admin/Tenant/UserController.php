<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\User\CreateUser;
use App\Actions\User\GetTenantUsers;
use App\Actions\User\UpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Core\User\StoreUserRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request, GetTenantUsers $getTenantUsers)
    {
        return Inertia::render('admin/tenant/Users/Index', [
            'users' => UserResource::collection($getTenantUsers->execute($request)),
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/tenant/Users/Create', [
            'roles' => Role::all(['id', 'name']),
        ]);
    }
    public function store(StoreUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->execute($request);
        return redirect()->route('tenant.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return Inertia::render('tenant.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $user->load('roles');
        return Inertia::render('admin/tenant/Users/Edit', [
            'user' => $user,
            'roles' => Role::all(['id', 'name']),
        ]);
    }
    public function update(UserUpdateRequest $request, User $user, UpdateUser $updateUser): RedirectResponse
    {
        try{
            $user = $updateUser->execute($request, $user);
        }catch (\Exception $e){
            return redirect()->route('tenant.users.edit', ['user' => $user])
                ->with('error', $e->getMessage());
        }


        return redirect()->route('tenant.users.edit', ['user' => $user])
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('tenant.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
