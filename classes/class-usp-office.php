<?php


class USP_Office {

	private $master_id = 0;
	private $master;
	protected static $_instance = null;

	public static function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		if ( self::$_instance ) {
			return;
		}
	}

	public function __clone() {
		return;
	}

	public function __wakeup() {
		return;
	}

	function setup($user_id){
		global $user_ID;

		if ( ! $this->is() ) {
			return;
		}

		if ( ! $user_id ) {
			$this->master_id = $user_ID;
		} else {
			$this->master_id = $user_id;
		}

		if ( $this->master_id ) {
			$this->master = USP()->user( $this->master_id );
		}
	}

	function is() {
		global $wp_query;

		if ( ! $office_page = USP()->options()->get( 'account_page' ) ) {
			return;
		}

		if ( ! $wp_query->is_main_query() ) {
			return;
		}

		if ( isset( $wp_query->queried_object ) ) {
			if ( $wp_query->queried_object->ID != $office_page ) {
				return;
			}
		} else if ( isset( $wp_query->query ) ) {
			if ( ! isset( $wp_query->query['page_id'] ) || $wp_query->query['page_id'] != $office_page ) {
				return;
			}
		}

		return true;
	}

	function is_master( $user_id ) {

		if ( ! $user_id || ! $this->is() ) {
			return false;
		}

		if ( $user_id != $this->master_id ) {
			return false;
		}

		return true;
	}

	function set_master( $user_id ) {
		$this->master_id = $user_id;
		$this->master    = USP()->user( $user_id );
	}

	function get_master_id() {
		return $this->master_id;
	}

	function get_master() {
		return $this->master;
	}

}