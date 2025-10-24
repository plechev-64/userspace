<?php
/**
 * Временный скрипт для отладки автозагрузки классов.
 * Запустите его из командной строки: php debug_autoload.php
 */

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

echo "--- Начинаем отладку автозагрузки ---\n";

// Попробуем загрузить классы, которые используются в FieldMapperTest
$classesToLoad = [
    \UserSpace\Tests\Unit\Common\Module\Form\Src\Infrastructure\FieldMapperTest::class,
    \UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper::class,
    \UserSpace\Common\Module\Form\Src\Infrastructure\Field\Boolean::class,
    \UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\BooleanAbstractFieldDto::class,
    \UserSpace\Common\Module\Form\Src\Domain\Field\DTO\AbstractFieldDto::class,
    \UserSpace\Core\String\StringFilterInterface::class,
    \Mockery\MockInterface::class, // Проверим Mockery
    \PHPUnit\Framework\TestCase::class, // Проверим PHPUnit
];

foreach ($classesToLoad as $className) {
    echo "Попытка загрузить: " . $className . "\n";
    try {
        if (str_contains($className, 'Interface')) { // Проверяем интерфейсы
            if (interface_exists($className)) {
                echo "  Успешно загружен интерфейс: " . $className . "\n";
            } else {
                echo "  НЕ НАЙДЕН интерфейс: " . $className . "\n";
            }
        } else { // Проверяем классы
            if (class_exists($className)) {
                echo "  Успешно загружен класс: " . $className . "\n";
            } else {
                echo "  НЕ НАЙДЕН класс: " . $className . "\n";
            }
        }
    } catch (\Throwable $e) {
        // Если произошла фатальная ошибка (например, синтаксическая), она будет поймана здесь
        echo "  ФАТАЛЬНАЯ ОШИБКА при загрузке " . $className . ": " . $e->getMessage() . "\n";
        echo "  Файл: " . $e->getFile() . ", Строка: " . $e->getLine() . "\n";
        exit(1); // Прекращаем выполнение, чтобы не пропустить ошибку
    }
}

echo "--- Отладка автозагрузки завершена ---\n";
?>