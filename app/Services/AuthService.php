<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Attempt login and issue a Sanctum token.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, string $deviceName = 'api'): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact your administrator.'],
            ]);
        }

        // Revoke existing tokens for this device to prevent token accumulation
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        $this->userRepository->updateLastLogin($user);

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user->load(['roles', 'permissions', 'branch', 'employee']),
        ];
    }

    /**
     * Revoke the current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Revoke all tokens (logout everywhere).
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Rotate the current token (invalidate old, issue new).
     */
    public function refreshToken(User $user, string $deviceName = 'api'): array
    {
        $user->currentAccessToken()->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token'      => $token,
            'token_type' => 'Bearer',
        ];
    }
}
