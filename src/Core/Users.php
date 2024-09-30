<?php

namespace USP\Core;

class Users {

	private array $users = [];
	private static $instance = null;

	public static function getInstance(): ?Users {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param User $user
	 *
	 * @return void
	 */
	public function add( User $user ) {

		if ( isset( $this->users[ $user->ID ] ) ) {
			return;
		}

		$this->users[ $user->ID ] = $user;
	}

	/**
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function isset( int $user_id ): bool {
		return isset( $this->users[ $user_id ] );
	}

	/**
	 * @param int $user_id
	 *
	 * @return null|User
	 */
	public function get( int $user_id ): ?User {
		return $this->users[ $user_id ] ?? null;
	}

	/**
	 * @return array
	 */
	public function get_all(): array {
		return $this->users;
	}

	public function remove( $user_id ): void {
		if ( isset( $this->users[ $user_id ] ) ) {
			unset( $this->users[ $user_id ] );
		}
	}


}