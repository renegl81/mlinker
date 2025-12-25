<?php

namespace App\Actions\User;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class UpdateUser
{
    public function execute(UserUpdateRequest $request, User $user): User
    {
        $data = $request->validated();
        if($data['password']){
            $data['password'] = bcrypt($data['password']);
        }else{
            $data['password'] = $user->password;
        }
        $user->update($data);

        return $user;
    }
}
