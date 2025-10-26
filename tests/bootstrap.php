<?php

/**
 * Test bootstrap
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

// Подключаем автозагрузчик Composer
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';