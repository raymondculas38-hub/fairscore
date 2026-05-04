<?php

$dir = __DIR__;

function searchFiles($path, $pattern) {
    global $dir;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir . '/' . $path));
    $files = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match($pattern, $file->getFilename())) {
            $files[] = str_replace('\\', '/', $file->getPathname());
        }
    }
    return $files;
}

$views = searchFiles('resources/views', '/\.blade\.php$/');
$controllers = searchFiles('app/Http/Controllers', '/\.php$/');
$models = searchFiles('app/Models', '/\.php$/');

$allPhpFiles = array_merge(
    searchFiles('app', '/\.php$/'),
    searchFiles('routes', '/\.php$/'),
    searchFiles('resources/views', '/\.php$/')
);

$unusedViews = [];
foreach ($views as $view) {
    // Normalise view path to get the blade name
    $relPath = str_replace(str_replace('\\', '/', $dir) . '/resources/views/', '', $view);
    $viewName = str_replace('.blade.php', '', $relPath);
    $viewDot = str_replace('/', '.', $viewName);
    
    $used = false;
    foreach ($allPhpFiles as $phpFile) {
        if ($phpFile === $view) continue;
        $content = file_get_contents($phpFile);
        if (strpos($content, $viewDot) !== false || strpos($content, "'" . $viewName . "'") !== false || strpos($content, '"' . $viewName . '"') !== false) {
            $used = true;
            break;
        }
    }
    if (!$used) {
        // Double check for partial names or dynamic includes
        $parts = explode('.', $viewDot);
        $lastPart = end($parts);
        $used2 = false;
        if (in_array($lastPart, ['index', 'create', 'edit', 'show', 'form', 'app', 'navigation', 'admin'])) {
            $used2 = false;
        } else {
             foreach ($allPhpFiles as $phpFile) {
                if ($phpFile === $view) continue;
                $content = file_get_contents($phpFile);
                if (strpos($content, $lastPart) !== false) {
                    $used2 = true;
                    break;
                }
            }
        }
        
        if (!$used2) {
            $unusedViews[] = $relPath;
        }
    }
}

$unusedControllers = [];
foreach ($controllers as $controller) {
    $controllerName = basename($controller, '.php');
    if ($controllerName === 'Controller') continue;
    
    $used = false;
    foreach ($allPhpFiles as $phpFile) {
        if ($phpFile === $controller) continue;
        $content = file_get_contents($phpFile);
        if (strpos($content, $controllerName) !== false) {
            $used = true;
            break;
        }
    }
    if (!$used) {
        $relPath = str_replace(str_replace('\\', '/', $dir) . '/', '', $controller);
        $unusedControllers[] = $relPath;
    }
}

$unusedModels = [];
foreach ($models as $model) {
    $modelName = basename($model, '.php');
    
    $used = false;
    foreach ($allPhpFiles as $phpFile) {
        if ($phpFile === $model) continue;
        $content = file_get_contents($phpFile);
        if (strpos($content, $modelName) !== false) {
            $used = true;
            break;
        }
    }
    if (!$used) {
        $relPath = str_replace(str_replace('\\', '/', $dir) . '/', '', $model);
        $unusedModels[] = $relPath;
    }
}

echo "Unused Views:\n";
print_r($unusedViews);
echo "\nUnused Controllers:\n";
print_r($unusedControllers);
echo "\nUnused Models:\n";
print_r($unusedModels);
