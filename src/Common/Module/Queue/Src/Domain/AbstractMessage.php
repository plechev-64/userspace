<?php

namespace UserSpace\Common\Module\Queue\Src\Domain;

use ReflectionClass;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Базовый класс для сообщений, реализующий сериализацию/десериализацию.
 */
abstract class AbstractMessage implements QueueableMessage {

	public function toArray(): array {
		$properties = ( new ReflectionClass( $this ) )->getProperties( \ReflectionProperty::IS_PUBLIC );
		$data       = [];
		foreach ( $properties as $property ) {
			$data[ $property->getName() ] = $property->getValue( $this );
		}

		return $data;
	}

	public static function fromArray( array $data ): static {
        // Используем Reflection для динамического вызова конструктора с нужными аргументами,
        // что необходимо для работы со свойствами readonly.
        $reflectionClass = new ReflectionClass(static::class);
        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            // Если нет конструктора, возвращаем пустой объект (для сообщений без свойств).
            return new static();
        }

        $constructorParams = $constructor->getParameters();
        $args = [];

        foreach ($constructorParams as $param) {
            $paramName = $param->getName();
            // Подставляем значение из данных, если оно есть, иначе null (позволит использовать значение по умолчанию).
            $args[] = $data[$paramName] ?? null;
        }

        return $reflectionClass->newInstanceArgs($args);
	}
}