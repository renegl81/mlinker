<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\User\CreateUser;
use App\Actions\User\GetTenantUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Jobs\DeleteUser;
use App\Jobs\ShowUser;
use App\Jobs\UpdateUser;
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

    public function store(UserStoreRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->execute($request);
        return redirect()->route('user.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return Inertia::render('tenant.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('admin/tenant/Users/Edit', [
            'user' => $user,
        ]);
    }
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        UpdateUser::dispatch($request, $user);

        return redirect()->route('tenant.users.show', ['user' => $user]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        DeleteUser::dispatch($user);

        return redirect()->route('tenant.users.index');
    }
}
