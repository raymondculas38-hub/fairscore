<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$_SESSION['user_id'] = 1;
require 'core/Database.php';
require 'core/Model.php';
require 'app/models/User.php';
$GLOBALS['auth_user'] = User::find(1);

require 'helpers/functions.php';
$_SERVER['HTTP_HOST'] = 'localhost:8000';
$_SERVER['SCRIPT_NAME'] = '/index.php';

echo "url('/admin/judges') => " . url('/admin/judges') . "\n";

// Also check the users table columns
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE users");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers table columns:\n";
foreach($cols as $c) {
    echo "  " . $c['Field'] . " (" . $c['Type'] . ")" . ($c['Null'] === 'NO' && empty($c['Default']) && $c['Extra'] !== 'auto_increment' ? " NOT NULL, NO DEFAULT" : "") . "\n";
}
