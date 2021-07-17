<?php


class USP_Office {

	private $owner_id = 0;
	private $on_page = 0;
	private $owner;
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

	function setup() {
		global $user_ID;

		if ( ! $office_page = USP()->options()->get( 'account_page' ) ) {
			return false;
		}

		if ( '' !== get_site_option( 'permalink_structure' ) ) {
			$url = parse_url( $_SERVER['REQUEST_URI'] );
			$path_parts = explode('/',trim($url['path'], '/'));
			if(!empty($path_parts[0])){
				if(get_post( $office_page )->post_name == $path_parts[0]){
					$this->on_page = 1;
				}
			}
		}else{
			if(!empty($_GET['page_id']) && $_GET['page_id'] == $office_page){
				$this->on_page = 1;
			}
		}

		$owner_id = !empty(USP()->get_var('member'))? USP()->get_var('member'): $user_ID;

		if ( $this->on_page && $owner_id ) {
			$this->set_owner( $owner_id );
		}

	}

	function on_page() {
		global $wp_query;

		if ( ! $wp_query->is_main_query() ) {
			return false;
		}

		return !empty($this->on_page);
	}

	function is_owner( $user_id ) {

		if ( ! $user_id ) {
			return false;
		}

		if ( $user_id != $this->owner_id ) {
			return false;
		}

		return true;
	}

	function set_owner( $user_id ) {
		$this->owner_id = $user_id;
		$this->owner    = USP()->user( $user_id );
	}

	function get_owner_id() {
		return $this->owner_id;
	}

	function owner() {
		return $this->owner;
	}

}