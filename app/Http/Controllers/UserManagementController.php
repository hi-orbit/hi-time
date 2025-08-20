<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::latest()->paginate(20);
        return view('settings.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('settings.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user,contractor,customer',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('settings.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        return view('settings.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('settings.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,user,contractor,customer',
        ]);

        $user->update($validated);

        return redirect()->route('settings.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the current user
        if ($user->id === Auth::id()) {
            return redirect()->route('settings.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show password reset form
     */
    public function showResetPassword(User $user)
    {
        return view('settings.users.reset-password', compact('user'));
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('settings.users.index')
            ->with('success', 'Password reset successfully.');
    }
}
