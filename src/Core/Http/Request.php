<?php

namespace UserSpace\Core\Http;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $headers;
    private array $routeParams = [];

    private function __construct(array $get, array $post, array $server)
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->headers = $this->extractHeaders($server);

        // Если это JSON-запрос, парсим тело и добавляем его в POST-параметры.
        $contentType = $this->getHeader('content-type');
        if ($contentType && stripos($contentType, 'application/json') !== false) {
            $jsonPayload = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonPayload)) {
                // Объединяем данные из JSON с обычными POST-данными (если они есть)
                $this->post = array_merge($this->post, $jsonPayload);
            }
        }
    }

    /**
     * Возвращает POST-параметры запроса.
     * @return array
     */
    public function getPostParams(): array
    {
        return $this->post;
    }

    /**
     * Создает экземпляр Request из глобальных переменных PHP.
     * @return static
     */
    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER);
    }

    public function getQuery(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function getPost(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function getRequestUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Возвращает путь из URI запроса (без query string).
     * @return string
     */
    public function getPathInfo(): string
    {
        return parse_url($this->getRequestUri(), PHP_URL_PATH) ?? '/';
    }

    public function getHeader(string $key, $default = null)
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function getRouteParam(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setRouteParam(string $key, $value): void
    {
        $this->routeParams[$key] = $value;
    }

    /**
     * Извлекает заголовки из массива $_SERVER.
     * @param array $server
     * @return array
     */
    private function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$headerKey] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                // Добавляем важные заголовки, которые не начинаются с HTTP_
                $headerKey = str_replace('_', '-', strtolower($key));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }

    /**
     * Возвращает GET-параметры запроса.
     * @return array
     */
    public function getGetParams(): array
    {
        return $this->get;
    }
}