<?php
$_SERVER['HTTPS']='off'; 
$_SERVER['HTTP_HOST']='localhost'; 
$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs'; 
$_SERVER['SCRIPT_FILENAME']='C:/Users/Admin/OneDrive/FairScore/fairscore/public/index.php'; 
$_SERVER['SCRIPT_NAME']='/fairscore/public/index.php';
require 'helpers/functions.php'; 

function robust_url($path = '') {
    $base = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $base .= "://" . $_SERVER['HTTP_HOST'];
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($dir === '/' || $dir === '\\') $dir = '';
    return $base . $dir . '/' . ltrim($path, '/');
}

echo "Current: " . url('admin/login') . "\n";
echo "Proposed: " . robust_url('admin/login') . "\n";
