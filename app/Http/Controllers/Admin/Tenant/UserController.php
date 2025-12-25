<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\User\GetTenantUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Jobs\CreateUser;
use App\Jobs\DeleteUser;
use App\Jobs\ListUsers;
use App\Jobs\ShowUser;
use App\Jobs\Tenant\TenantListUsers;
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

    public function store(UserStoreRequest $request): RedirectResponse
    {
        CreateUser::dispatch($request);

        return redirect()->route('user.index');
    }

    public function show(Request $request, User $user)
    {
        ShowUser::dispatch($user);

        return Inertia::render('user.show', [
            'user' => $user,
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        UpdateUser::dispatch($request, $user);

        return redirect()->route('user.show', ['user' => $user]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        DeleteUser::dispatch($user);

        return redirect()->route('user.index');
    }
}
