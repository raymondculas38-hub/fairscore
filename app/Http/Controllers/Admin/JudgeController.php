<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JudgeController extends Controller
{
    public function index()
    {
        $judges = User::where('role', 'JUDGE')->with('events')->latest()->get();
        return view('admin.judges.index', compact('judges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role'     => 'JUDGE',
        ]);

        return back()->with('success', 'Judge account created!');
    }

    public function update(Request $request, User $judge)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $judge->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $judge->update($data);
        return back()->with('success', 'Judge updated!');
    }

    public function destroy(User $judge)
    {
        $judge->delete();
        return back()->with('success', 'Judge account removed.');
    }
}
