<?php

namespace UserSpace\Core\Mutex;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface MutexInterface {
	/**
	 * Пытается установить блокировку.
	 *
	 * @param string $lockKey Уникальный ключ блокировки.
	 * @param int    $ttl     Время жизни блокировки в секундах (Time To Live).
	 *
	 * @return bool True, если блокировка успешно установлена, иначе false.
	 */
	public function acquireLock( string $lockKey, int $ttl ): bool;

	/**
	 * Снимает блокировку.
	 *
	 * @param string $lockKey Уникальный ключ блокировки.
	 */
	public function releaseLock( string $lockKey ): void;
}