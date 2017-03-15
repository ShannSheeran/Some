<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
// Setup autoloading
require 'init_autoloader.php';
require 'config.php';
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
/* require 'UploadHandler.php';
$uploadhandler = new UploadHandler(); */
session_start();
header('Content-Type:text/html; charset=utf-8');
date_default_timezone_set('PRC');
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

