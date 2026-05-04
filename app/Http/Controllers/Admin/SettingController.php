<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use App\Models\Score;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'array',
        ]);

        foreach ($validated['settings'] ?? [] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget('app_settings');

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function factoryReset(Request $request)
    {
        Score::truncate();
        
        return redirect()->back()->with('success', 'Factory reset completed. All scores have been wiped.');
    }
}
