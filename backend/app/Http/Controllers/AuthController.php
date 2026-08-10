<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\AuthVerifyEmailRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    public function verifyEmail(AuthVerifyEmailRequest $request): JsonResponse
    {
        $result = $this->authService->verifyEmail($request->validated());

        return response()->json($result);
    }

    public function login(AuthLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success.',
        ]);
    }
}
