<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\actions\CreateUserAction;
use Modules\Authentication\actions\LoginAction;
use Modules\Authentication\Http\Requests\LoginRequest;
use Modules\Authentication\Http\Requests\RegisterRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    public function register(RegisterRequest $request, CreateUserAction $action): JsonResponse
    {
        return DB::transaction(function () use ($request, $action) {
            $response = $action->handle($request->all());
            return successResponse(
                message: 'Registration successful',
                data: $response
            );
        });
    }

    public function login(LoginRequest $request, LoginAction $action)
    {
        return DB::transaction(function () use ($request, $action) {
            $data = $action->handle($request->email, $request->password);

            return successResponse('Logged in successfully.', $data);
        });
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return successResponse('Logged out successfully.');
    }
}
