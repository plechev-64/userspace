<?php

/**
 * Test bootstrap
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/debug_errors.log');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

// Подключаем автозагрузчик Composer
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
//
//// Проверяем автозагрузку
//echo "=== ПРОВЕРКА АВТОЗАГРУЗКИ ===\n";
//
//// Проверяем основные классы
//$testClasses = [
//    'UserSpace\Plugin',
//    'UserSpace\Common\Module\Form\Src\Infrastructure\Field\Boolean',
//    'UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanAbstractFieldDto',
//];
//
//foreach ($testClasses as $className) {
//    if (class_exists($className)) {
//        echo "✓ Класс найден: {$className}\n";
//    } else {
//        echo "✗ Класс НЕ найден: {$className}\n";
//
//        // Показываем пути поиска
//        $paths = $autoloader->getPrefixesPsr4();
//        echo "  PSR-4 пути:\n";
//        foreach ($paths as $prefix => $pathArray) {
//            echo "  - {$prefix} => " . implode(', ', $pathArray) . "\n";
//        }
//    }
//}
//
//echo "=== КОНЕЦ ПРОВЕРКИ ===\n\n";