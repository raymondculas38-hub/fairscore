<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * Because the "Continue with Google" button is rendered ONLY on the
     * Admin Panel tab of the login page, every Google sign-up/sign-in
     * is treated as an admin action.  New accounts are created with
     * role = ADMIN, and existing JUDGE accounts that were previously
     * mis-created are promoted to ADMIN automatically.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $googleId    = $googleUser->getId();
            $googleEmail = $googleUser->getEmail();
            $googleName  = $googleUser->getName();

            Log::info('Google OAuth callback', compact('googleId', 'googleEmail', 'googleName'));

            // ── Step 1: Match by google_id ────────────────────────────────
            $existingUser = User::where('google_id', $googleId)->first();

            if ($existingUser) {
                // Ensure the user has ADMIN role (fix legacy JUDGE mis-assignment)
                if (!$existingUser->isAdmin()) {
                    $existingUser->update(['role' => 'ADMIN']);
                    Log::info("Promoted user {$existingUser->username} from {$existingUser->role} to ADMIN");
                }

                Auth::login($existingUser);
                $request->session()->regenerate();
                return $this->redirectBasedOnRole($existingUser->fresh());
            }

            // ── Step 2: Match by email (link account) ─────────────────────
            $userByEmail = User::where('email', $googleEmail)->first();

            if ($userByEmail) {
                $userByEmail->update([
                    'google_id' => $googleId,
                    'role'      => 'ADMIN',  // Google OAuth = Admin Panel
                ]);

                Log::info("Linked Google to existing user {$userByEmail->username}, set role=ADMIN");

                Auth::login($userByEmail);
                $request->session()->regenerate();
                return $this->redirectBasedOnRole($userByEmail->fresh());
            }

            // ── Step 3: Create new ADMIN account ──────────────────────────
            $usernamePrefix = explode('@', $googleEmail)[0];
            $username = $usernamePrefix;

            // Ensure username uniqueness
            while (User::where('username', $username)->exists()) {
                $username = $usernamePrefix . rand(1000, 9999);
            }

            $newUser = User::create([
                'name'      => $googleName,
                'email'     => $googleEmail,
                'username'  => $username,
                'google_id' => $googleId,
                'password'  => Str::random(32),
                'role'      => 'ADMIN',  // Google OAuth button is Admin-only
            ]);

            Log::info("Created new ADMIN user via Google: {$username} ({$googleEmail})");

            Auth::login($newUser);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($newUser);

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            return redirect()->route('login')->with(
                'error',
                'Google authentication failed. Please try again or use your username and password.'
            );
        }
    }

    /**
     * Strictly role-based redirect. Uses redirect()->route() instead of
     * redirect()->intended() to prevent stale session URLs from sending
     * users to the wrong panel.
     */
    private function redirectBasedOnRole(User $user)
    {
        session()->forget('url.intended');

        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('judge.dashboard');
    }
}
