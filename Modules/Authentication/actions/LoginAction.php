<?php

namespace Modules\Authentication\actions;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Authentication\dto\UserData;
use Modules\Authentication\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginAction
{
    /**
     * @throws ValidationException
     */
    public function handle($email, $password)
    {
        $user = User::where('email', $email)->first();

        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Invalid login credentials.',
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'user' => UserData::from($user)
        ];


    }
}
