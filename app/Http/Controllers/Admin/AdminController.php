<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_events'    => Event::count(),
            'live_events'     => Event::where('status', 'live')->count(),
            'total_judges'    => User::where('role', 'JUDGE')->count(),
            'total_scores'    => Score::count(),
        ];

        $activeEvents = Event::whereIn('status', ['upcoming', 'live'])->latest()->take(5)->get();
        $liveEvents   = Event::where('status', 'live')->with(['participants', 'judges', 'scores'])->get();

        return view('admin.dashboard', compact('stats', 'activeEvents', 'liveEvents'));
    }

    /**
     * Create a new admin account from the dashboard "Create Account" card.
     */
    public function createAccount(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'username.unique' => 'This username is already taken.',
            'email.unique'    => 'This email is already registered.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => $validated['password'], // Auto-hashed via User model cast
            'role'     => 'ADMIN',
        ]);

        return back()->with('success', "Admin account \"{$validated['username']}\" created successfully.");
    }

    /**
     * Remind a judge to submit their scores for an event.
     */
    public function remindJudge(Event $event, User $judge)
    {
        try {
            \Illuminate\Support\Facades\Http::post('http://localhost:3000/api/emit-reminder', [
                'judge_id'   => $judge->id,
                'event_id'   => $event->id,
                'event_name' => $event->name,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', "Failed to send real-time reminder. Ensure the Socket.IO server is running.");
        }

        return back()->with('success', "Reminder sent to {$judge->name}.");
    }
}
