<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManageUserController extends Controller
{
    // public function __construct()
    // {
    //     // Only admin and support can manage users
    //     $this->middleware(['role:admin|support']);
    // }

    /**
     * GET /api/users
     * Fetch all users (Admin / Support)
     */
    public function index()
    {
        $users = User::select(['id', 'name', 'email', 'status', 'created_at', 'updated_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }


    /**
     * POST /api/users
     * Create a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email',
            'role'   => 'required|string|in:user,support,admin',
        ]);

        $user = User::create([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'password' => Hash::make('password'), // Secure
            'role'   => $validated['role'],
        ]);

        $user->assignRole($validated['role']);

        return response()->json($user->fresh(), 201);
    }


    /**
     * PUT /api/users/{id}
     * Update a user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'email'  => "sometimes|email|unique:users,email,{$id}",
            'role'   => 'sometimes|string|in:user,support,admin',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        $user->update($validated);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json($user);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:active,inactive',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'user' => $user
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Delete a user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself (optional safety)
        if (auth()->id() === $user->id) {
            return response()->json(['message' => "You cannot delete your own account."], 403);
        }

        $user->delete();

        return response()->json(['message' => "User deleted successfully"]);
    }
}
