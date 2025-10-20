<?php

namespace UserSpace\Core\Asset;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс для управления и подключения CSS и JavaScript ассетов.
 * Абстрагирует функции WordPress wp_enqueue_style, wp_enqueue_script, wp_localize_script.
 */
interface AssetRegistryInterface
{
    /**
     * Регистрирует CSS-стили для последующего подключения.
     *
     * @param string $handle Имя (хэндл) стиля.
     * @param string $src URL файла стилей.
     * @param string[] $deps Массив хэндлов стилей, от которых зависит данный стиль.
     * @param string|bool|null $ver Версия стиля.
     * @param string $media Тип медиа, для которого предназначен стиль.
     */
    public function registerStyle(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, string $media = 'all'): void;

    /**
     * Регистрирует JavaScript-скрипт для последующего подключения.
     *
     * @param string $handle Имя (хэндл) скрипта.
     * @param string $src URL файла скрипта.
     * @param string[] $deps Массив хэндлов скриптов, от которых зависит данный скрипт.
     * @param string|bool|null $ver Версия скрипта.
     * @param bool $inFooter Подключать ли скрипт в футере.
     */
    public function registerScript(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, bool $inFooter = false): void;

    /**
     * Подключает CSS-стили.
     *
     * @param string $handle Имя (хэндл) стиля.
     * @param string $src URL файла стилей.
     * @param string[] $deps Массив хэндлов стилей, от которых зависит данный стиль.
     * @param string|bool|null $ver Версия стиля.
     * @param string $media Тип медиа, для которого предназначен стиль.
     */
    public function enqueueStyle(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, string $media = 'all'): void;

    /**
     * Подключает JavaScript-скрипт.
     *
     * @param string $handle Имя (хэндл) скрипта.
     * @param string $src URL файла скрипта.
     * @param string[] $deps Массив хэндлов скриптов, от которых зависит данный скрипт.
     * @param string|bool|null $ver Версия скрипта.
     * @param bool $inFooter Подключать ли скрипт в футере.
     */
    public function enqueueScript(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, bool $inFooter = false): void;

    /**
     * Локализует скрипт, передавая ему данные с бэкенда.
     *
     * @param string $handle Хэндл скрипта, к которому привязываются данные.
     * @param string $objectName Имя JavaScript-объекта, который будет содержать данные.
     * @param array<string, mixed> $data Данные для передачи.
     */
    public function localizeScript(string $handle, string $objectName, array $data): void;

    /**
     * Собирает URL'ы для всех скриптов и стилей, поставленных в очередь.
     *
     * @return array{scripts: string[], styles: string[], localized: array<int, array{objectName: string, data: array<string, mixed>}>}
     */
    public function getAssets(): array;

    /**
     * Очищает очереди скриптов и стилей.
     * Полезно для тестирования или изоляции ассетов.
     */
    public function clear(): void;
}