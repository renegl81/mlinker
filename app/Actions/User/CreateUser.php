<?php

namespace App\Actions\User;

use App\Http\Requests\Admin\Core\User\StoreUserRequest;
use App\Models\User;

class CreateUser
{
    public function execute(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->input('roles', []));
        }

        return $user;
    }
}
