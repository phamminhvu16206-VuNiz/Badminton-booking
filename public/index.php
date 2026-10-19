<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('APP_ROOT', dirname(__DIR__));
define('APP_BASE_URL', rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php'))), '/'));

require APP_ROOT . DIRECTORY_SEPARATOR . 'autoload.php';
require APP_ROOT . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Controller.php';
require APP_ROOT . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'App.php';

(new Core\App())->run();
