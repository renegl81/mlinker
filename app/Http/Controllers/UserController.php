<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\CreateUser;
use App\Jobs\DeleteUser;
use App\Jobs\ListUsers;
use App\Jobs\ShowUser;
use App\Jobs\UpdateUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        ListUsers::dispatch();

        return Inertia::render('user.index', [
            'users' => $users,
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        CreateUser::dispatch($request);

        return redirect()->route('user.index');
    }

    public function show(Request $request, User $user)
    {
        ShowUser::dispatch($id);

        return Inertia::render('user.show', [
            'user' => $user,
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        UpdateUser::dispatch($request, $id);

        return redirect()->route('user.show', ['user' => $user]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        DeleteUser::dispatch($id);

        return redirect()->route('user.index');
    }
}
