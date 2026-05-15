<?php

namespace App\Http\Controllers\Api;

// File: app/Http/Controllers/Api/AuthController.php

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Authenticate user and issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:100',
        ]);

        $user = User::where('email', $request->email)
                    ->where('is_active', true)
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Revoke existing tokens from this device (prevents token proliferation)
        $user->tokens()->where('name', $request->device_name)->delete();

        // Issue new token with abilities based on role
        $abilities = $user->isAdmin()
            ? ['admin', 'equipment:read', 'equipment:write', 'booking:read', 'booking:write']
            : ['member', 'equipment:read', 'equipment:checkout', 'booking:read', 'booking:create'];

        $token = $user->createToken(
            $request->device_name,
            $abilities,
            now()->addDays(30) // Token expires in 30 days
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data'    => [
                'user'       => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'role'              => $user->role,
                    'membership_number' => $user->membership_number,
                    'phone'             => $user->phone,
                ],
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toDateTimeString(),
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Return authenticated user data.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'checkouts' => fn ($q) => $q->active()->with('equipment:id,name,code'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data'    => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'membership_number' => $user->membership_number,
                'phone'             => $user->phone,
                'active_checkouts'  => $user->checkouts,
            ],
        ]);
    }
