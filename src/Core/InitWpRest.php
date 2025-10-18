<?php

namespace UserSpace\Core;

use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Helper\RestHelper;
use UserSpace\Core\Rest\RestApi;
use Exception;

class InitWpRest
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        /*
         * Меняем префикс рест апи wordpress
         */
        add_filter('rest_url_prefix', fn($prefix) => $this->container->get('rest.prefix'));

        /**
         * @var RestHelper $restHelper
         */
        $restHelper = $this->container->get(RestHelper::class);

        if (!$restHelper->isRestRequest()) {
            return;
        }

        //$this->processSensitiveEndpoints();

        /**
         * @var RestApi $rest
         */
        $rest = $this->container->get(RestApi::class);

        /*
         * Если запрос на эндпоинт ядра wordpress, то нет смысла регистрировать наши эндпоинты
         */
        if ($restHelper->isOnWpEndpointRequest()) {
            return;
        }

        /*
         * Если запрос НЕ на эндпоинт ядра wordpress,
         * то отключим регистрацию стандартных роутов wordpress, что экономит до 50мс на генерации
         */
        //remove_action('rest_api_init', 'create_initial_rest_routes', 99);

        /*
         * Регистрируем в wp роуты добавленные в контейнер
         */
        add_action('rest_api_init', fn() => $rest->registerRestRoutes());

        // Подключаем наш обработчик для корректной отправки JsonResponse
        add_filter('rest_pre_serve_request', [$rest, 'serveRequest'], 10, 2);
    }

    /**
     * Закрываем эндпоинты с чувствительной информацией от гостей
     *
     * @throws Exception
     */
    public function processSensitiveEndpoints(): void
    {
        /**
         * @var Request $request
         */
        $request = $this->container->get(Request::class);
        $requestUri = rtrim($request->getPathInfo(), '/');
        $restPrefix = $this->container->get('rest.prefix');
        $restNamespace = $this->container->get('rest.namespace');

        $authRequired = [
            'equal' => [
                "/$restPrefix",
                "/$restPrefix/$restNamespace",
                "/$restPrefix/batch/v1",
            ],
            'start_with' => [
                "/$restPrefix/wp/v2",
                "/$restPrefix/oembed",
                "/$restPrefix/wp-block-editor",
                "/$restPrefix/wp-site-health",
            ],
        ];

        add_action('rest_api_init', function () use ($authRequired, $requestUri) {

            $sensitive = in_array(strtolower($requestUri), array_map('strtolower', $authRequired['equal']));

            if (!$sensitive) {
                foreach ($authRequired['start_with'] as $path) {
                    if (str_starts_with(strtolower($requestUri), strtolower($path))) {
                        $sensitive = true;
                        break;
                    }
                }
            }

            if ($sensitive && !is_user_logged_in()) {
                die('Not allowed');
            }

        }, 1);
    }
}