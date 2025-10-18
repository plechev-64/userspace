<?php

namespace UserSpace\Core\Rest\Attributes;

use Attribute;

#[Attribute]
class Route
{

    /**
     * @param string $path - string path or regex
     */
    public function __construct(
        public string $path,
        /**
         * Методы запроса которые поддерживает endpoint
         *
         * Например: 'POST' или 'GET,POST'
         *
         * @var string
         */
        public string $method = 'GET',

        /**
         * Требуемая возможность для доступа к роуту
         *
         * значение из $permission будет использоваться в current_user_can
         *
         * @var string
         */
        public string $permission = '',

        /**
         * True если эндпоинт не требует загрузки wordpress
         *
         * @var bool
         */
        public bool $isFastApi = false,

        /**
         * Неймспейс эндпоинта, если не указан - используетс стандартный
         *
         * @var string
         */
        public string $namespace = '',

        /**
         * В какой среде будет доступен эндпоинт
         *
         * ['development', 'staging', 'production']
         *
         * @var array
         */
        public array $env = [],
    )
    {
    }
}