<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showAdminLogin()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('judge.dashboard');
        }
        return view('auth.admin_login');
    }

    public function showAdminRegister()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('judge.dashboard');
        }
        return view('auth.admin_register');
    }

    public function registerAdmin(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'address'         => 'required|string|max:255',
            'username'        => 'required|string|max:100',
            'password'        => [
                'required',
                'min:8',
                'regex:/[a-zA-Z]/', // must contain at least one letter
                'regex:/[0-9]/',    // must contain at least one number
            ],
        ]);

        $username = $validated['username'];

        // Check if username/email already exists
        if (\App\Models\User::where('username', $username)->orWhere('email', $username)->exists()) {
            return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
        }

        \App\Models\User::create([
            'name'     => $validated['name'],
            'address'  => $validated['address'],
            'username' => $username,
            'email'    => $username, // using username as email fallback
            'password' => $validated['password'], // Cast handles hashing
            'role'     => 'ADMIN',
        ]);

        return redirect()->route('admin.login')->with('success', 'Admin account created! You can now log in.');
    }

    public function showJudgeLogin()
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('judge.dashboard');
        }
        return view('auth.judge_login');
    }

    public function login(Request $request)
    {
        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        if ($request->input('intended_role') === 'admin') {
            $rules['password'] = [
                'required',
                'min:8',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
            ];
        }

        $credentials = $request->validate($rules, [
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.min'   => 'Password must be at least 8 characters long.',
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Flush any stale "intended URL" stored by the auth middleware.
            // redirect()->intended() would follow that URL regardless of role,
            // causing admins to land on the judge panel and vice-versa.
            $request->session()->forget('url.intended');

            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('judge.dashboard');
        }

        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $role = Auth::check() ? Auth::user()->role : null;
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($role === 'JUDGE') {
            return redirect()->route('judge.login');
        }
        
        return redirect()->route('admin.login');
    }
}
