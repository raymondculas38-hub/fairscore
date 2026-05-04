<?php

class SettingController extends Controller
{
    private function checkAuth()
    {
        if (!auth() || !auth()->isAdmin()) {
            $this->redirect(url('/admin/login'));
        }
    }

    public function index()
    {
        $this->checkAuth();
        
        $settingsList = Setting::all();
        $settings = [];
        foreach ($settingsList as $s) {
            $settings[$s->key] = $s;
        }
        
        return $this->view('admin.settings.index', compact('settings'));
    }

    public function update()
    {
        $this->checkAuth();
        check_csrf();

        $settings = $_POST['settings'] ?? [];

        foreach ($settings as $key => $value) {
            $existing = Setting::firstWhere('key', '=', $key);
            if ($existing) {
                $existing->update(['value' => $value]);
            } else {
                Setting::create(['key' => $key, 'value' => $value]);
            }
        }

        with('success', 'Settings updated successfully.');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function factoryReset()
    {
        $this->checkAuth();
        check_csrf();
        
        $db = Model::getDb();
        $db->exec("TRUNCATE TABLE scores");
        
        with('success', 'Factory reset completed. All scores have been wiped.');
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
