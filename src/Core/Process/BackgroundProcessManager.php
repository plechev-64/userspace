<?php

namespace UserSpace\Core\Process;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Управляет запуском фоновых (неблокирующих) процессов.
 */
class BackgroundProcessManager
{
    /**
     * Отправляет неблокирующий POST-запрос для запуска фоновой задачи.
     *
     * @param string $endpoint Относительный путь к REST-эндпоинту (например, '/queue/run-worker').
     * @param array $body Тело запроса.
     */
    public function dispatch(string $endpoint, array $body = []): void
    {
        $url = rest_url('/'.USERSPACE_REST_NAMESPACE.'/' . ltrim($endpoint, '/'));

        // Собираем куки текущего пользователя для аутентификации в фоновом запросе.
        $cookies = [];
        if (!empty($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                $cookies[] = new \WP_Http_Cookie(['name' => $name, 'value' => $value]);
            }
        }

        // Если определена константа для Docker-хоста, подменяем localhost.
        // Это решает проблему с loopback-запросами внутри Docker.
        if (defined('USERSPACE_DOCKER_HOST') && USERSPACE_DOCKER_HOST) {
            $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
            $site_port = wp_parse_url(home_url(), PHP_URL_PORT);
            $local_domain = $site_port ? "{$site_host}:{$site_port}" : $site_host;

            if (str_contains($url, $local_domain)) {
                $url = str_replace($local_domain, USERSPACE_DOCKER_HOST, $url);
            }
        }

        $args = [
            'blocking' => false,
            'timeout' => 1,
            'sslverify' => false, // Для локальной разработки
            'cookies' => $cookies,
            'body' => $body,
        ];

        wp_remote_post($url, $args);
    }
}