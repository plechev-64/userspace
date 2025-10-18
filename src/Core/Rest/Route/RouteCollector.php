<?php

namespace UserSpace\Core\Rest\Route;

class RouteCollector
{
    /**
     * @var RouteData[]
     */
    private array $routes = [];
    private string $currentGroup = '';

    public function __construct()
    {

    }

    public function addGroup(string $groupPath, callable $callback): RouteCollector
    {
        $prevCurrentGroup = $this->currentGroup;
        $this->currentGroup = $groupPath;
        $callback($this);
        $this->currentGroup = $prevCurrentGroup;

        return $this;
    }

    public function currentGroup(): string
    {
        return $this->currentGroup;
    }

    public function addRoute(RouteData $routeData): RouteCollector
    {
        $this->routes[] = $routeData;

        return $this;
    }

    public function all(): array
    {
        return $this->routes;
    }
}