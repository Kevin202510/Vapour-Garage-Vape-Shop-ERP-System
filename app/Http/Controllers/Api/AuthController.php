<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email:      $request->email,
            password:   $request->password,
            deviceName: $request->input('device_name', 'api')
        );
            
        return response()->json([
            'message'    => 'Login successful.',
            'token'      => $result['token'],
            'token_type' => $result['token_type'],
            'user'       => new UserResource($result['user']),
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * POST /api/auth/logout-all  — revoke all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json(['message' => 'Logged out from all devices.']);
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken(
            user:       $request->user(),
            deviceName: $request->input('device_name', 'api')
        );

        return response()->json([
            'message'    => 'Token refreshed.',
            'token'      => $result['token'],
            'token_type' => $result['token_type'],
        ]);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions', 'branch', 'employee']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
