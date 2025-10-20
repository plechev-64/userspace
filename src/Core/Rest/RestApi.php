<?php

namespace UserSpace\Core\Rest;

use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Helper\RestHelper;
use UserSpace\Core\Rest\Route\RouteArgsResolver;
use UserSpace\Core\Rest\Route\RouteCollector;
use UserSpace\Core\Rest\Route\RouteData;
use UserSpace\Core\Rest\Route\RouteHandler;
use UserSpace\Core\User\UserApiInterface;
use UserSpace\Core\Rest\Route\RouteParser;
use WP_REST_Response;

class RestApi
{
    private bool $parsed = false;

    public function __construct(
        private readonly array              $controllers,
        private readonly string             $namespace,
        private readonly RouteParser        $routeParser,
        private readonly ContainerInterface $container,
        private readonly UserApiInterface   $userApi
    )
    {

    }

    public function parse(): void
    {
        foreach ($this->controllers as $routeController) {
            $this->routeParser->parse($routeController);
        }

        $this->parsed = true;
    }

    public function collector(): RouteCollector
    {
        return $this->routeParser->getCollector();
    }

    /**
     * Регистрация роутов в wp rest
     *
     * @return void
     */
    public function registerRestRoutes(): void
    {
        if (!$this->parsed) {
            $this->parse();
        }

        foreach ($this->collector()->all() as $route) {

            if ($route->isFastApi()) {
                continue;
            }
            /*if ($route->getEnv() && !in_array(WP_ENV, $route->getEnv())) {
                continue;
            }*/

            $this->registerRoute($route);
        }

    }

    private function registerRoute(RouteData $routeData): void
    {
        $args = [
            'methods' => $routeData->getActions(),
            'callback' => $this->routeCallback($routeData),
            'permission_callback' => function () use ($routeData) {
                $permission = $routeData->getPermission();
                return !$permission || $this->userApi->currentUserCan($permission);
            }
        ];

        register_rest_route(
            $routeData->getNamespace() ?: $this->namespace,
            $routeData->getPath(),
            $args
        );

    }

    /**
     * Метод возвращает функцию которая вызывает обработчик эндпоинта
     *
     * @param RouteData $routeData
     * @return \Closure
     */
    public function routeCallback(RouteData $routeData): \Closure
    {
        $container = $this->container;

        return static function () use ($routeData, $container) {
            /**
             * @var Request $request
             * @var RestHelper $restHelper
             */
            $request = $container->get(Request::class);
            $restHelper = $container->get(RestHelper::class);
            $matches = $restHelper->matchesFromRequestByRoute($routeData);

            /*
             * Дополним реквест переменными из роута
             */
            foreach ($matches as $k => $v) {
                if (!is_int($k)) {
                    $request->setRouteParam($k, $v);
                }
            }

            $handler = new RouteHandler(
                $routeData,
                $container,
                $container->get(RouteArgsResolver::class)
            );

            /*
             * Для совместимости с FastApi ответ сразу выводим,
             * не возвращаем его в wp
             */
            // try {
            //     $response = $handler->handle($request);
            // } catch (RestException $e) {
            //     $response = new JsonResponse(['message' => $e->getMessage()], 400);
            // } catch (NotFoundEntityException $e) {
            //     $response = new JsonResponse(['message' => $e->getMessage()], 404);
            // } catch (\Exception $e) {
            //     /*
            //      * Все остальные исключения в сообщении которых могут быть чувствительные данные
            //      */
            //     $response = new JsonResponse(['message' => __('An unexpected error occurred.', 'usp')], 500);
            // }
            //
            // return $response;
            return $handler->handle($request);
        };
    }

    /**
     * Перехватывает ответ перед отправкой и, если это наш JsonResponse,
     * отправляет его самостоятельно.
     *
     * @param bool $served Отправлен ли уже ответ.
     * @param \WP_REST_Response|\WP_Error|JsonResponse $result Результат работы эндпоинта.
     * @return bool
     */
    public function serveRequest(bool $served, WP_REST_Response $result): bool
    {
        $data = $result->get_data();
        if ($data instanceof JsonResponse) {
            $data->send();
        }

        return $served;
    }
}