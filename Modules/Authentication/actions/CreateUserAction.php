<?php

namespace Modules\Authentication\actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\dto\UserData;
use Modules\Authentication\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreateUserAction
{
    public function handle(array $data)
    {
           $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($data['password']),
            ]);

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user' => UserData::from($user)
        ];

    }
}
