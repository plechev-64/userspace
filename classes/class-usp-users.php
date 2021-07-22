<?php


class USP_Users {

	private $users = [];
	private static $instance = null;

	public static function getInstance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		return;
	}

	public function __clone() {
		return;
	}

	public function __wakeup() {
		return;
	}

	/**
	 * @param USP_User $user
	 *
	 * @return void
	 */
	public function add( USP_User $user ) {

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
	public function isset( $user_id ) {
		return isset( $this->users[ $user_id ] );
	}

	/**
	 * @param int $user_id
	 *
	 * @return false|USP_User
	 */
	public function get( $user_id ) {
		return $this->users[ $user_id ] ?? false;
	}

	/**
	 * @return array
	 */
	public function get_all() {
		return $this->users;
	}

	public function remove( $user_id ) {
		if ( isset( $this->users[ $user_id ] ) ) {
			unset( $this->users[ $user_id ] );
		}
	}


}