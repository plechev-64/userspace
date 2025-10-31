<?php

namespace UserSpace\Core\Mutex;

use UserSpace\Common\Module\Settings\Src\Domain\TransientApiInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TransientMutex implements MutexInterface {

	public function __construct(
		private readonly TransientApiInterface $transientApi
	) {
	}

	public function acquireLock( string $lockKey, int $ttl ): bool {
		// Проверяем, не установлена ли уже блокировка
		if ( $this->transientApi->get( $lockKey ) ) {
			return false; // Блокировка уже существует
		}

		// Устанавливаем транзиент-блокировку со временем жизни
		return $this->transientApi->set( $lockKey, true, $ttl );
	}

	public function releaseLock( string $lockKey ): void {
		$this->transientApi->delete( $lockKey );
	}
}