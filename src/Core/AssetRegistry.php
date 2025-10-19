<?php

namespace UserSpace\Core;

/**
 * Регистрирует ассеты (скрипты и стили) во время AJAX/REST запросов.
 * Стандартный механизм wp_enqueue_script/style не работает в REST-контексте,
 * поэтому мы собираем хэндлы ассетов здесь, чтобы затем передать их на фронтенд.
 */
class AssetRegistry
{
    /**
     * Собирает URL'ы для скриптов и стилей, зарегистрированных в реестре.
     *
     * @return array{scripts: string[], styles: string[], localized: array}
     */
    public function getAssets(): array
    {
        $scripts = [];
        $styles = [];
        $localized = [];

        $wp_scripts = wp_scripts();
        $wp_styles = wp_styles();

        // Собираем стили из очереди
        foreach ($wp_styles->queue as $handle) {
            if (isset($wp_styles->registered[$handle]) && !empty($wp_styles->registered[$handle]->src)) {
                $dependency = $wp_styles->registered[$handle];
                $src = $dependency->src;
                $styles[] = add_query_arg('ver', $dependency->ver, $src);
            }
        }

        // Собираем скрипты из очереди
        foreach ($wp_scripts->queue as $handle) {
            if (isset($wp_scripts->registered[$handle]) && !empty($wp_scripts->registered[$handle]->src)) {
                $dependency = $wp_scripts->registered[$handle];
                $src = $dependency->src;
                $scripts[] = add_query_arg('ver', $dependency->ver, $src);

                // Проверяем, есть ли для этого скрипта локализованные данные
                if (!empty($wp_scripts->registered[$handle]->extra['data'])) {
                    // Пытаемся найти имя объекта (например, 'uspL10n')
                    preg_match("/var (.+?) =/m", $wp_scripts->registered[$handle]->extra['data'], $matches);
                    if (isset($matches[1])) {
                        $objectName = trim($matches[1]);
                        // Извлекаем JSON-данные
                        $json_data = substr($wp_scripts->registered[$handle]->extra['data'], strpos($wp_scripts->registered[$handle]->extra['data'], '{'));
                        $json_data = rtrim($json_data, ';');
                        $data = json_decode($json_data, true);

                        if ($data) {
                            $localized[] = compact('objectName', 'data');
                        }
                    }
                }
            }
        }

        return compact('scripts', 'styles', 'localized');
    }

    public function clear(): void
    {
        wp_scripts()->queue = [];
        wp_styles()->queue = [];
    }
}