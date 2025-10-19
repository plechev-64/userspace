<?php

namespace UserSpace\Core\Http;

class JsonResponse
{
    public function __construct(
        private mixed $data = null,
        private int   $statusCode = 200,
        private array $headers = []
    )
    {
    }

    /**
     * Отправляет ответ клиенту и завершает выполнение скрипта.
     */
    public function send(): void
    {
        // Устанавливаем код ответа
        status_header($this->statusCode);

        // Устанавливаем заголовки
        $this->headers['Content-Type'] = 'application/json; charset=UTF-8';
        foreach ($this->headers as $key => $value) {
            header(sprintf('%s: %s', $key, $value));
        }

        // Если нет данных, отправляем пустой ответ
        if ($this->data === null) {
            echo '';
            die();
        }

        // Кодируем и выводим данные
        $json = wp_json_encode($this->data, JSON_UNESCAPED_UNICODE);

        echo $json;
        die();
    }
}