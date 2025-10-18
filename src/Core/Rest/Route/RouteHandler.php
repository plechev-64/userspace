<?php

namespace UserSpace\Core\Rest\Route;

use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Http\JsonResponse;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Exception\RestException;

class RouteHandler
{
    public function __construct(
        private readonly RouteData $routeData,
        private readonly ContainerInterface $di,
        private readonly RouteArgsResolver $argsResolver
    )
    {
    }

    /**
     * @throws \ReflectionException
     * @throws RestException
     */
    public function handle(Request $request): JsonResponse
    {
        $controller = $this->di->get($this->routeData->getController());
        $method = $this->routeData->getMethod();
        $args = $this->argsResolver->resolve($controller, $method, $request);
        return $controller->$method(...$args);
    }
}