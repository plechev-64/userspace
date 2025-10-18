<?php

namespace UserSpace\Core\Rest\Helper;

use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Route\RouteData;

class RestHelper
{

    public function __construct(
        private readonly Request $request,
        private readonly string  $prefix,
        private readonly string  $namespace,
    )
    {
    }

    public function matchesFromRequestByRoute(RouteData $routeData): array
    {
        $restPrefix = $this->prefix;
        $restNamespace = $this->namespace;

        $routePath = trim($routeData->getPath(), '/');
        $fullRoutePath = "/$restPrefix/$restNamespace/$routePath";

        // Получаем путь для сопоставления в зависимости от типа постоянных ссылок
        $requestPath = $this->request->getQuery('rest_route');
        if ($requestPath === null) {
            // Для "красивых" ссылок
            $requestPath = rtrim(parse_url($this->request->getRequestUri(), PHP_URL_PATH), '/');
            preg_match('@^' . $fullRoutePath . '$@i', $requestPath, $matches);
        } else {
            // Для "простых" ссылок (plain permalinks)
            $fullRoutePathWithoutPrefix = "/$restNamespace/$routePath";
            preg_match('@^' . $fullRoutePathWithoutPrefix . '$@i', $requestPath, $matches);
        }

        return $matches;
    }

    /**
     * Проверка что текущий запрос на rest эндпоинт
     *
     * @return boolean
     */
    public function isRestRequest(): bool
    {
        // Проверка на "красивые" постоянные ссылки (e.g. /wp-json/...)
        if (str_starts_with(strtolower($this->request->getRequestUri()), '/' . strtolower($this->prefix))) {
            return true;
        }

        // Проверка на "простые" постоянные ссылки (e.g. /index.php?rest_route=/...)
        return $this->request->getQuery('rest_route') !== null;
    }

    /**
     * Проверка что текущий запрос на рест эндпоинт зарегистрированный самим wordpress
     *
     * @return boolean
     */
    public function isOnWpEndpointRequest(): bool
    {
        $restPrefix = $this->prefix;
        $requestUri = $this->request->getRequestUri();
        $wpPaths = [
            "/$restPrefix/wp",
            "/$restPrefix/wp-site-health",
            "/$restPrefix/oembed",
            "/$restPrefix/wp-block-editor",
        ];

        foreach ($wpPaths as $path) {
            if (str_starts_with(strtolower($requestUri), strtolower($path))) {
                return true;
            }
        }

        return false;

    }
}