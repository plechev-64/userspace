<?php

namespace UserSpace\Core\Container;

use Exception;
use ReflectionClass;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Простой контейнер для внедрения зависимостей (DI Container).
 *
 * Управляет жизненным циклом объектов (сервисов) в плагине.
 */
class Container implements ContainerInterface
{

    /**
     * Массив для хранения фабрик, создающих сервисы.
     * @var array<string, callable>
     */
    private array $factories = [];

    /**
     * Массив для хранения уже созданных экземпляров сервисов (синглтонов).
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Регистрирует фабрику для создания сервиса.
     *
     * @param string $id Идентификатор сервиса (обычно имя класса).
     * @param callable $factory Функция-замыкание, которая создает экземпляр сервиса.
     */
    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->instances[$id]); // Очищаем кэшированный экземпляр, если фабрика обновляется
    }

    public function unset(string $id): void
    {
        unset($this->factories[$id]);
    }

    /**
     * Возвращает экземпляр сервиса по его идентификатору.
     * При первом вызове создает его с помощью фабрики и кэширует.
     *
     * @template T
     * @param class-string<T> $id Идентификатор сервиса (имя класса).
     * @return T Экземпляр сервиса.
     * @throws Exception Если сервис не зарегистрирован в контейнере.
     */
    public function get(string $id)
    {
        // 1. Проверяем, есть ли уже готовый экземпляр
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // 2. Проверяем, есть ли для него явная фабрика
        if (isset($this->factories[$id])) {
            $this->instances[$id] = $this->factories[$id]($this);
            return $this->instances[$id];
        }

        // 3. Пытаемся создать экземпляр автоматически через рефлексию
        $this->instances[$id] = $this->build($id);
        return $this->instances[$id];
    }

    /**
     * Проверяет, зарегистрирован ли сервис в контейнере.
     *
     * @param string $id Идентификатор сервиса.
     * @return bool
     */
    public function has(string $id): bool
    {
        // Сервис существует, если для него есть фабрика или если такой класс в принципе существует
        return isset($this->factories[$id]) || class_exists($id);
    }

    /**
     * Создает новый экземпляр класса, не кэшируя его.
     *
     * @template T
     * @param class-string<T> $id Идентификатор сервиса (имя класса).
     * @param array<string, mixed> $parameters Ассоциативный массив параметров для конструктора.
     * @return T Новый экземпляр сервиса.
     * @throws \Exception Если сервис не может быть создан.
     */
    public function build(string $id, array $parameters = [])
    {
        if (!$this->has($id)) {
            throw new Exception("Service or class '{$id}' not found.");
        }

        $reflection = new ReflectionClass($id);

        if (!$reflection->isInstantiable()) {
            throw new Exception("Class '{$id}' is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            // Если нет конструктора, просто создаем экземпляр
            return new $id();
        }

        $constructorParameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($constructorParameters as $parameter) {
            $paramName = $parameter->getName();
            $paramType = $parameter->getType();

            // 1. Проверяем, был ли параметр передан вручную
            if (array_key_exists($paramName, $parameters)) {
                $dependencies[] = $parameters[$paramName];
                continue;
            }

            // 2. Если это объект (не встроенный тип), пытаемся разрешить его из контейнера
            if ($paramType && !$paramType->isBuiltin()) {
                $dependencyClass = $paramType->getName();
                $dependencies[] = $this->get($dependencyClass);
                continue;
            }

            // 3. Если это скалярный тип и у него есть значение по умолчанию
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            // Если не удалось разрешить зависимость, можно бросить исключение
            throw new Exception("Cannot resolve parameter '{$paramName}' for class '{$id}'");
        }

        return new $id(...$dependencies);
    }
}