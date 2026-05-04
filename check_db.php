<?php
require 'core/Database.php';
require 'core/Model.php';

$db = Model::getDb();
$stmt = $db->query("SELECT id, name, admin_id FROM events");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
