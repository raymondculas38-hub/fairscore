<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'core/Database.php';
require 'core/Model.php';
require 'core/Controller.php';
require 'helpers/functions.php';

// Autoloader
spl_autoload_register(function ($class) {
    if (file_exists('app/Models/' . $class . '.php')) {
        require 'app/Models/' . $class . '.php';
    }
});

// Mock Session
$_SESSION['user_id'] = 1;

require 'app/controllers/EventController.php';

$controller = new EventController();
try {
    // Pass event ID 1 (or whatever exists)
    $controller->breakdown(1);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
} catch (Error $e) {
    echo "Error: " . $e->getMessage();
}
