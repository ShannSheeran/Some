<?php
ini_set('date.timezone', 'Asia/Shanghai');
define('THINK_PATH', './ThinkPHP/');
define('APP_NAME', 'Api');
define('APP_PATH', './Api/');
define("WEB_ROOT", str_replace("\\",'/',dirname(__FILE__)) . "/");
define('WEB_CACHE_PATH', WEB_ROOT."Cache/");//ÍøÕ¾µ±Ç°Â·¾¶
define("RUNTIME_PATH", WEB_ROOT . "Cache/Runtime/Api/");
define('APP_DEBUG', true);

require(THINK_PATH . "ThinkPHP.php");
?>