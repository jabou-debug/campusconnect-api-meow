<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * POST /api/v1/auth/register
     * Create a new user account and return a Sanctum token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "name"             => "required|string|max:255",
            "email"            => "required|string|email|max:255|unique:users",
            "password"         => "required|string|min:8|confirmed",
            "student_id"       => "nullable|string|max:20",
            "role"             => "nullable|in:student,faculty,admin",
        ]);

        $user = User::create([
            "name"       => $validated["name"],
            "email"      => $validated["email"],
            "password"   => Hash::make($validated["password"]),
            "student_id" => $validated["student_id"] ?? null,
            "role"       => $validated["role"] ?? "student",
        ]);

        $token = $user->createToken("auth_token")->plainTextToken;

        return $this->successResponse([
            "user"  => $user,
            "token" => $token,
        ], "Registration successful.", 201);
    }

    /**
     * POST /api/v1/auth/login
     * Validate credentials and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email"    => "required|email",
            "password" => "required|string",
        ]);

        $user = User::where("email", $validated["email"])->first();

        if (!$user || !Hash::check($validated["password"], $user->password)) {
            return $this->errorResponse("Invalid email or password.", 401);
        }

        // Revoke old tokens to keep one active session per device (optional)
        $token = $user->createToken("auth_token")->plainTextToken;

        return $this->successResponse([
            "user"  => $user,
            "token" => $token,
        ], "Login successful.");
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke the current Sanctum token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, "Logged out successfully.");
    }

    /**
     * GET /api/v1/auth/me
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), "Authenticated user retrieved.");
    }
}
