<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
// Setup autoloading
require 'init_autoloader.php';
require 'config.php';
/*  ini_set('display_errors', 1);
 error_reporting(E_ALL); */
/* require 'UploadHandler.php';
$uploadhandler = new UploadHandler(); */
session_start();
header('Content-Type:text/html; charset=utf-8');
date_default_timezone_set('PRC');

/* // 日志
$content = date('Y-m-d H:i:s') . "\n";
$content .= var_export($_REQUEST, true) . "\n";
$content .= var_export($_SERVER, true) . "\n";
$file = __DIR__ . '/uploadfiles/visit.txt';
$data = '';
if (is_file($file)) {
    $data = file_get_contents($file);
}
file_put_contents($file, $content . $data);
 */
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
