<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {

            $user = Auth::user();

            if ($user->status === "inactive") {
                Auth::logout();
                return response()->json([
                    'message' => 'Your account is inactive. Please contact support or admin.',
                ], 403);
            }

            // Create token for the user
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'user' => $user->load('roles'),
                'token' => $token, // Add token to response
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials.',
        ], 401);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
