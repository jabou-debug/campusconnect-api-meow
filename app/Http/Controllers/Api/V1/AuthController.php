<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * POST /api/v1/auth/register
     * Create a new student account and return a Sanctum token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password'   => ['required', 'confirmed', Password::min(8)],
            'student_id' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'student_id' => $validated['student_id'] ?? null,
            'role'       => 'student', // new registrations are always students
        ]);

        $token = $user->createToken('campusconnect-mobile')->plainTextToken;

        return $this->createdResponse(
            [
                'user'       => $this->formatUser($user),
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
            'Account created successfully. Welcome to CampusConnect!'
        );
    }

    /**
     * POST /api/v1/auth/login
     * Verify credentials, revoke old tokens, issue a new token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Generic message — never reveal which field is wrong (prevents enumeration)
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse(
                'Invalid credentials. Please check your email and password.',
                401
            );
        }

        // Single-session enforcement: revoke all existing tokens before issuing new one
        $user->tokens()->delete();

        $token = $user->createToken('campusconnect-mobile')->plainTextToken;

        return $this->successResponse(
            [
                'user'       => $this->formatUser($user),
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
            "Login successful. Welcome back, {$user->name}!"
        );
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke only the token used in this request (single-device logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            null,
            'Logged out successfully. Your session has been ended.'
        );
    }

    /**
     * POST /api/v1/auth/logout-all
     * Revoke ALL tokens for this user (all-device logout).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $count = $request->user()->tokens()->count();
        $request->user()->tokens()->delete();

        return $this->successResponse(
            null,
            "Logged out from {$count} device(s) successfully."
        );
    }

    /**
     * GET /api/v1/auth/me
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->formatUser($request->user()),
            'Profile retrieved successfully.'
        );
    }

    /**
     * PUT /api/v1/auth/profile
     * Update the authenticated user's name and/or student_id.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'       => ['sometimes', 'string', 'max:255'],
            'student_id' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return $this->successResponse(
            $this->formatUser($user->fresh()), // fresh() reloads from DB
            'Profile updated successfully.'
        );
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    /**
     * Format a user for API responses.
     * NOTE: password is excluded here AND via User::$hidden — double protection.
     */
    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'student_id' => $user->student_id,
            'role'       => $user->role,
            'created_at' => $user->created_at->toDateTimeString(),
            'updated_at' => $user->updated_at->toDateTimeString(),
        ];
    }
}