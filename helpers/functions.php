<?php

function view($view, $data = []) {
    extract($data);
    $viewFile = BASE_PATH . '/app/views/' . str_replace('.', '/', $view) . '.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        die("View $view not found.");
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function auth() {
    // Simple auth check helper, replace with proper Auth class later if needed
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Lazy load user
    if (!isset($GLOBALS['auth_user'])) {
        $GLOBALS['auth_user'] = User::find($_SESSION['user_id']);
    }
    return $GLOBALS['auth_user'];
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['_token'])) {
            die("CSRF token validation failed.");
        }
    }
}

function url($path = '') {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Derive sub-folder base from SCRIPT_NAME (e.g. /fairscore/public/index.php → /fairscore/public)
    // For PHP built-in server or root-hosted Apache, dirname gives '/' or '\'
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $baseDir    = rtrim(dirname($scriptName), '/');
    if ($baseDir === '.' || $baseDir === '\\' || $baseDir === '/') $baseDir = '';

    return $scheme . '://' . $host . $baseDir . '/' . ltrim($path, '/');
}


function asset($path) {
    return url($path);
}

// Session flash messages
function with($key, $value) {
    $_SESSION['flash'][$key] = $value;
}

function session($key) {
    if (isset($_SESSION['flash'][$key])) {
        $val = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $val;
    }
    return $_SESSION[$key] ?? null;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function route($name, $params = []) {
    $map = [
        'admin.login'            => 'admin/login',
        'admin.register'         => 'admin/register',
        'admin.register.post'    => 'admin/register',
        'login.post'             => 'login',
        'admin.dashboard'        => 'admin/dashboard',
        
        'admin.events.index'     => 'admin/events',
        'admin.events.create'    => 'admin/events/create',
        'admin.events.store'     => 'admin/events',
        'admin.events.edit'      => 'admin/events/{id}/edit',
        'admin.events.toggle'    => 'admin/events/{id}/toggle',
        'admin.events.destroy'   => 'admin/events/{id}/delete',
        'admin.events.breakdown' => 'admin/events/{id}/breakdown',
        'admin.events.update'    => 'admin/events/{id}',
        
        'admin.participants.index'   => 'admin/events/{id}/participants',
        'admin.participants.store'   => 'admin/events/{id}/participants',
        'admin.participants.update'  => 'admin/events/{id}/participants/{p_id}',
        'admin.participants.destroy' => 'admin/events/{id}/participants/{p_id}/delete',
        
        'admin.criteria.index'   => 'admin/events/{id}/criteria',
        'admin.criteria.store'   => 'admin/events/{id}/criteria',
        'admin.criteria.update'  => 'admin/events/{id}/criteria/{c_id}',
        'admin.criteria.destroy' => 'admin/events/{id}/criteria/{c_id}/delete',
        
        'admin.judges.index'     => 'admin/judges',
        'admin.judges.store'     => 'admin/judges',
        'admin.judges.update'    => 'admin/judges/{id}',
        'admin.judges.destroy'   => 'admin/judges/{id}/delete',
        
        'admin.scoreboard.index' => 'admin/scoreboard',
        'admin.scoreboard.show'  => 'admin/scoreboard/{id}',
        'admin.scoreboard.setDisplay' => 'admin/scoreboard/{id}/set-display',
        
        'admin.settings.index'   => 'admin/settings',
        'admin.settings.update'  => 'admin/settings',
        'admin.settings.factory_reset' => 'admin/settings/factory-reset',
        
        'logout'                 => 'logout',
        
        'judge.dashboard'        => 'judge/dashboard',
        'judge.login'            => 'judge/login',
        'judge.score'            => 'judge/event/{id}/score',
        'judge.score.verify'     => 'judge/event/{id}/pin',
        'judge.event.leave'      => 'judge/event/{id}/leave',
        'judge.notifications.markAllRead' => 'judge/notifications/read',
        'judge.notifications.delete' => 'judge/notifications/{id}/delete',
        
        'leaderboard.show'       => 'live/{id}',
        
        'auth.google'            => 'auth/google',
    ];
    $path = $map[$name] ?? str_replace('.', '/', $name);

    if (!is_array($params)) $params = [$params];
    
    // Replace placeholders {id} with actual parameters
    foreach ($params as $param) {
        $val = is_object($param) ? $param->id : $param;
        if (strpos($path, '{') !== false) {
            $path = preg_replace('/\{[^}]+\}/', $val, $path, 1);
        } else {
            $path .= '/' . $val;
        }
    }
    return url($path);
}

function vite($assets) {
    if (is_string($assets)) $assets = [$assets];
    $html = '';
    // Use the compiled assets from public/build/manifest.json if it exists
    $manifestPath = BASE_PATH . '/public/build/manifest.json';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        foreach ($assets as $asset) {
            if (isset($manifest[$asset])) {
                $file = url('build/' . $manifest[$asset]['file']);
                if (str_ends_with($file, '.css')) {
                    $html .= '<link rel="stylesheet" href="'.$file.'">';
                } else {
                    $html .= '<script type="module" src="'.$file.'"></script>';
                }
            }
        }
    } else {
        // Fallback to dev server
        $html .= '<script type="module" src="http://localhost:5173/@vite/client"></script>';
        foreach ($assets as $asset) {
            $html .= '<script type="module" src="http://localhost:5173/'.$asset.'"></script>';
        }
    }
    return $html;
}

class RequestMock {
    public function routeIs($pattern) {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        if ($base_dir === '/' || $base_dir === '\\') {
            $base_dir = '';
        }
        if ($base_dir !== '' && strpos($uri, $base_dir) === 0) {
            $uri = substr($uri, strlen($base_dir));
        }
        $uri = ltrim($uri, '/');
        
        $pattern = str_replace(['admin.', '.*'], ['admin/', '*'], $pattern);
        $patternStr = str_replace('*', '.*', preg_quote($pattern, '/'));
        
        return preg_match('/^' . $patternStr . '/', $uri);
    }
}

function request() {
    return new RequestMock();
}

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        // Simple caching for the current request
        static $settings = null;
        if ($settings === null) {
            $settings = [];
            if (class_exists('Setting')) {
                try {
                    $all = Setting::all();
                    foreach ($all as $s) {
                        $settings[$s->key] = $s->value;
                    }
                } catch (\Throwable $e) {
                    // DB not ready or settings table doesn't exist
                }
            }
        }
        return $settings[$key] ?? $default;
    }
}
