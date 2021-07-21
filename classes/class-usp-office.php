<?php


class USP_Office {

	private $owner_id = 0;
	private $on_page = 0;
	private $owner;
	private $vars = [];
	private $varnames = array(
		'member' => 'user'
	);
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

	function get_var( $var_key ) {
		return ! empty( $this->vars[ $var_key ] ) ? $this->vars[ $var_key ] : false;
	}

	function setup() {
		global $user_ID, $wp_rewrite;

		if ( ! $office_page_id = USP()->options()->get( 'account_page' ) ) {
			return false;
		}

		$office_page = get_post( $office_page_id );

		$slugmatch = $office_page->post_name;

		if ( $wp_rewrite->using_index_permalinks() && $wp_rewrite->root == 'index.php/' ) {
			$slugmatch = 'index.php/' . $office_page->post_name;
		}

		add_rewrite_rule( $slugmatch . '/([^/]+)/?$', 'index.php?pagename=' . $office_page->post_name . '&' . $this->varnames['member'] . '=$matches[1]', 'top' );

		add_filter( 'query_vars', function ( $vars ) {
			$vars[] = $this->varnames['member'];

			return $vars;
		} );

		if ( '' !== get_site_option( 'permalink_structure' ) ) {
			$url        = parse_url( $_SERVER['REQUEST_URI'] );
			$path_parts = explode( '/', trim( $url['path'], '/' ) );
			if ( ! empty( $path_parts[0] ) ) {
				if ( $office_page->post_name == $path_parts[0] ) {
					$this->on_page = 1;
				}
			}
			if ( ! empty( $path_parts[1] ) ) {
				if ( $office_page->post_name == $path_parts[0] ) {
					$owner_id = $path_parts[1];
					if ( ! is_numeric( $owner_id ) && $user = get_user_by( 'slug', $owner_id ) ) {
						$owner_id = $user->ID;
					}

					$this->vars['member'] = $owner_id;
				}

			}
		} else {

			if ( ! empty( $_GET['page_id'] ) && $_GET['page_id'] == $office_page_id ) {
				$this->on_page = 1;
			}

			$this->vars['member'] = ! empty( $_GET[ $this->varnames['member'] ] ) ? $_GET[ $this->varnames['member'] ] : 0;

		}

		$owner_id = ! empty( $owner_id ) ? $owner_id : $user_ID;

		if ( $this->on_page && $owner_id ) {
			$this->set_owner( $owner_id );
		}

	}

	function on_page() {
		global $wp_query;

		if ( ! $wp_query->is_main_query() ) {
			return false;
		}

		return ! empty( $this->on_page );
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

	function owner(): USP_User {
		return $this->owner;
	}

}