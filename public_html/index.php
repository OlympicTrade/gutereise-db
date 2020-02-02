<?php
use Zend\Mvc\Application;

chdir(dirname(__DIR__));
mb_internal_encoding("UTF-8");
define('REQUEST_MICROTIME', microtime(true));
define('MODULE_DIR', realpath(__DIR__ . '/../module'));
define('PUBLIC_DIR', realpath(__DIR__));
define('DATA_DIR', realpath(__DIR__ . '/../data'));
define('ONLINE', true);
define('MODE', getenv('APP_ENV') ?: 'dev');
define('ADMIN_PREFIX', '');
/*
switch($_COOKIE['mode']) {
    case 'dev':
        define('MODE', 'dev');
        break;
    case 'test':
        define('MODE', 'test');
        break;
    default:
        define('MODE', 'public');
        break;
}*/

if(MODE == 'dev') {
    define('SYNC_DOMAIN', 'https://gutereise');
} else {
    define('SYNC_DOMAIN', 'https://test.gutereise.ru');
}

function d($data = '') {
    echo '<pre>' . "\n";
    var_dump($data);
    echo "\n" . '</pre>' . "\n";
}

function dd($data = '') {
    d($data);
    die();
}

error_reporting(E_ERROR/* | E_WARNING */| E_PARSE | E_COMPILE_ERROR  | E_COMPILE_WARNING | E_CORE_ERROR | E_RECOVERABLE_ERROR);

include __DIR__ . '/../vendor/autoload.php';

$appConfig = require __DIR__ . '/../config/application.config.php';
Application::init($appConfig)->run();