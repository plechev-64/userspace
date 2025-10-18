<?php

namespace UserSpace\Core\Rest\Route;

use ReflectionClass;
use ReflectionMethod;
use UserSpace\Core\Rest\Attributes\Route;

class RouteParser
{

    public function __construct(private readonly RouteCollector $collector)
    {
    }

    public function getCollector(): RouteCollector
    {
        return $this->collector;
    }

    /**
     * @throws \ReflectionException
     */
    public function parse(string $routeClass): void
    {
        $classReflection = new ReflectionClass($routeClass);
        $attrs = $classReflection->getAttributes(Route::class);

        $classRoute = isset($attrs[0])? $attrs[0]->getArguments()['path'] : '';

        $this->collector->addGroup($classRoute, function (RouteCollector $collector) use ($classReflection) {

            $methods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $attrs = $method->getAttributes(Route::class);

                if (!isset($attrs[0])) {
                    continue;
                }

                $endpointOptions = $attrs[0]->getArguments();

                $routeData = new RouteData(
                    $classReflection->getName(),
                    $method->getName(),
	                $this->collector->currentGroup() . $endpointOptions['path'],
                    $endpointOptions['namespace'] ?? '',
                    $endpointOptions['permission'] ?? '',
                    $endpointOptions['method'] ?? 'GET',
                    $endpointOptions['isFastApi'] ?? false,
                    $endpointOptions['env'] ?? []
                );

                $collector->addRoute($routeData);
            }
        });
    }
}
