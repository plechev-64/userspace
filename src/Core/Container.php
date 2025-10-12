<?php

namespace USP\Core;

use USP\Core\Module\Tabs\Tabs;
use USP\UserSpace;

final class Container {
	private static ?Container $instance = null;
	private array $services = [];
	private array $resolved = [];

	/**
	 * Конструктор сделан приватным, чтобы предотвратить создание через new.
	 */
	private function __construct() {
		$this->registerServices();
	}

	/**
	 * Получение единственного экземпляра контейнера.
	 * @return Container
	 */
	public static function getInstance(): Container {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Добавляет определение сервиса в контейнер.
	 *
	 * @param string $id Идентификатор сервиса (обычно имя класса).
	 * @param callable $resolver Функция-замыкание, которая создает сервис.
	 */
	public function add( string $id, callable $resolver ): void {
		$this->services[ $id ] = $resolver;
	}

	/**
	 * Получает сервис из контейнера. Создает его при первом вызове.
	 *
	 * @param string $id Идентификатор сервиса.
	 *
	 * @return mixed
	 * @throws \Exception Если сервис не найден.
	 */
	public function get( string $id ) {
		if ( isset( $this->resolved[ $id ] ) ) {
			return $this->resolved[ $id ];
		}

		if ( ! isset( $this->services[ $id ] ) ) {
			throw new \Exception( "Service not found: {$id}" );
		}

		$this->resolved[ $id ] = $this->services[ $id ]( $this );

		return $this->resolved[ $id ];
	}

	/**
	 * Регистрация основных сервисов плагина.
	 */
	private function registerServices(): void {
		// Явно указываем, как создавать каждый сервис.
		// Это решает проблему с приватными конструкторами и Singleton'ами.
		$this->add( Options::class, function () {
			return Options::getInstance();
		} );

		$this->add( Users::class, function () {
			return Users::getInstance();
		} );

		$this->add( Office::class, function () {
			return Office::getInstance();
		} );

		$this->add( Tabs::class, function () {
			return Tabs::instance();
		} );

		$this->add( Themes::class, function () {
			return new Themes();
		} );

		// Главный класс плагина, который зависит от других сервисов.
		$this->add( UserSpace::class, function ( Container $container ) {
			return new UserSpace(
				$container->get( Users::class ),
				$container->get( Office::class ),
				$container->get( Tabs::class ),
				$container->get( Themes::class ),
				$container->get( Options::class )
			);
		} );
	}
}