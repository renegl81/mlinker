<?php

namespace App\Http\Controllers\Admin\Core;

use App\Actions\User\GetAdminUsers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Core\User\StoreUserRequest;
use App\Http\Requests\Admin\Core\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request, getAdminUsers $getAdminUsers)
    {
        return Inertia::render('admin/Users/Index', [
            'users' => UserResource::collection($getAdminUsers->execute($request)),
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Users/Create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        User::create($data);

        return Redirect::route('users.index');
    }

    public function edit(User $user)
    {
        return Inertia::render('admin/Users/Edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return Redirect::route('users.index')->with('success', __('messages.users.form.edit_success'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return Redirect::route('users.index');
    }
}
