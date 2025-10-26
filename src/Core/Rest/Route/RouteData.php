<?php

namespace UserSpace\Core\Rest\Route;

class RouteData
{

    public function __construct(
        private readonly string $controller,
        private readonly string $method,
        private readonly string $path,
        private readonly string $namespace,
        private readonly string $permission,
        private readonly string $actions,
        private readonly bool   $isFastApi,
        private readonly array  $env,
    )
    {
    }

    public function isFastApi(): bool
    {
        return $this->isFastApi;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getEnv(): array
    {
        return $this->env;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getActions(): string
    {
        return $this->actions;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }
}
