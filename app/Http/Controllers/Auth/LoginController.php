<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/beranda';

    /**
     * Create a new controller instance.
     * 
     * REMOVE MIDDLEWARE - Handle it in routes instead
     */
    public function __construct()
    {
        // Remove this line that's causing the error:
        // $this->middleware('guest')->except('logout');
        
        // We'll handle middleware in routes/web.php instead
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('beranda');
        }
        
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Log login activity
            Log::info('User logged in', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip' => $request->ip()
            ]);

            // Redirect based on role
            $redirectRoute = $this->getRedirectRoute($user->role);
            
            return redirect()->intended($redirectRoute)
                           ->with('success', 'Selamat datang, ' . $user->full_name . '!');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->except('password'));
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout activity
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'ip' => $request->ip()
            ]);
        }

        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
                       ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Quick login for development
     */
    public function quickLogin(Request $request)
    {
        if (app()->environment('production')) {
            abort(404);
        }

        $username = $request->get('as', 'admin');
        $user = User::where('username', $username)->first();

        if ($user) {
            Auth::login($user);
            return redirect()->route('beranda')
                           ->with('success', 'Quick login sebagai ' . $user->full_name);
        }

        return redirect()->route('login')
                       ->with('error', 'User tidak ditemukan');
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        return view('auth.profile', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Check current password if new password provided
        if ($request->filled('password')) {
            if (!$request->filled('current_password') || 
                !Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Password saat ini tidak benar.'
                ]);
            }
        }

        // Update user data
        $updateData = [
            'full_name' => $request->full_name,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return back()->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Get redirect route based on role
     */
    private function getRedirectRoute($role)
    {
        switch ($role) {
            case 'admin':
                return route('beranda');
            case 'manager':
                return route('beranda');
            case 'staff':
                return route('beranda');
            default:
                return route('beranda');
        }
    }
}