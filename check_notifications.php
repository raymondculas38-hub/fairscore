<?php
require 'core/Database.php';
require 'core/Model.php';
$db = Model::getDb();
$stmt = $db->query('DESCRIBE notifications');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
