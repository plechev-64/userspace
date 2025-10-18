<?php

namespace UserSpace\Core\Rest\Route;

use UserSpace\Core\Rest\Abstract\AbstractController;
use UserSpace\Core\Rest\Exception\ResolverException;
use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Http\Request;

class RouteArgsResolver
{
    public function __construct(
        private readonly ContainerInterface $container
    )
    {
    }

    /**
     * @throws \ReflectionException
     * @throws ResolverException
     */
    public function resolve(object $controller, string $method, Request $request): array
    {
        $methodReflection = new \ReflectionMethod($controller, $method);
        $callbackParams = $methodReflection->getParameters();
        $args = [];

        foreach ($callbackParams as $param) {
            $paramType = $param->getType();
            if (!$paramType) {
                // Невозможно определить тип, пропускаем
                continue;
            }

            $paramTypeName = $paramType->getName();

            if ($paramType->isBuiltin()) {
                $args[] = $this->resolveScalar($param, $request);
                continue;
            }

            if ($paramTypeName === Request::class) {
                $args[] = $request;
                continue;
            }

            // Пытаемся получить сервис из контейнера
            if ($this->container->has($paramTypeName)) {
                $args[] = $this->container->get($paramTypeName);
            }
        }

        return $args;
    }

    /**
     * @throws ResolverException|\ReflectionException
     */
    private function resolveScalar(\ReflectionParameter $parameter, Request $request): string|int|bool|float|null
    {
        $paramName = $parameter->getName();
        $paramValue = $request->getRouteParam($paramName) ?? $request->getQuery($paramName) ?? $request->getPost($paramName);

        if ($paramValue === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            if ($parameter->allowsNull()) {
                return null;
            }
            throw new ResolverException(sprintf("Scalar parameter '%s' is required and was not found in the request.", $paramName));
        }

        // Приведение типов
        return match ($parameter->getType()->getName()) {
            'int' => (int)$paramValue,
            'string' => (string)$paramValue,
            'bool' => filter_var($paramValue, FILTER_VALIDATE_BOOLEAN),
            'float' => (float)$paramValue,
            default => $paramValue,
        };
    }
}