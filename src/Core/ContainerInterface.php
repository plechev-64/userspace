<?php

namespace UserSpace\Core;

use Exception;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Описывает интерфейс контейнера, который предоставляет методы для чтения его записей.
 */
interface ContainerInterface {
	/**
	 * Находит запись контейнера по ее идентификатору и возвращает ее.
	 *
	 * @template T
	 * @param class-string<T> $id Идентификатор записи для поиска.
	 *
	 * @return T Запись.
	 * @throws Exception Если запись не найдена.
	 */
	public function get( string $id );

	/**
	 * Возвращает true, если контейнер может вернуть запись для данного идентификатора.
	 * В противном случае возвращает false.
	 *
	 * @param string $id Идентификатор записи для поиска.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool;
}