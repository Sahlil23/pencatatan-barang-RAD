<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10);

        // Statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'admin_users' => User::admins()->count(),
            'staff_users' => User::staffs()->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,staff',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'role.required' => 'Role wajib dipilih',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => User::STATUS_ACTIVE,
        ]);

        return redirect()->route('users.index')
                        ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Load relationships
        $user->load('stockTransactions.item');
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,staff',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = [
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
                        ->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting current user
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus user yang sedang login');
        }

        // Prevent deleting if user has transactions
        if ($user->stockTransactions()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus user yang memiliki riwayat transaksi');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'User berhasil dihapus');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === User::STATUS_ACTIVE 
                    ? User::STATUS_INACTIVE 
                    : User::STATUS_ACTIVE;

        $user->update(['status' => $newStatus]);

        $statusText = $newStatus === User::STATUS_ACTIVE ? 'diaktifkan' : 'dinonaktifkan';
        
        return back()->with('success', "User berhasil {$statusText}");
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $newPassword = 'password123'; // Default password
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return back()->with('success', "Password user direset ke: {$newPassword}");
    }
}