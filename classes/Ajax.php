<?php

final class Ajax {

	public string $name;
	public bool $rest = false;
	public string $rest_space = 'userspace';
	public string $rest_route = '';
	public string $rest_callback = '';
	public array $ajax_callbacks = [];

	public static function getInstance() {
		static $instance;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	private function __construct() {
		static $hasInstance = false;

		if ( $hasInstance ) {
			return;
		}

		$hasInstance = true;
	}

	public function is_rest_request(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_REQUEST['rest_route'] ) && $_REQUEST['rest_route'] == '/' . $this->rest_space . '/' . $this->rest_route . '/';
	}

	public function init_ajax_callback( $callback, $guest_access = false, $modules = false ): bool {

		if ( ! $this->is_rest_request() ) {
			return false;
		}

		$this->ajax_callbacks[ $callback ] = [ 'guest' => $guest_access, 'modules' => $modules ];
		return true;
	}

	public function get_ajax_callback( $callback ): ?string {
		return $this->ajax_callbacks[ $callback ] ?? null;
	}

	public function init_rest( $rest_callback ) {

		$this->rest_callback = $rest_callback;
		$this->rest_route    = $rest_callback;

		add_action( 'rest_api_init', [ $this, 'register_route' ] );
	}

	public function register_route() {

		register_rest_route( $this->rest_space, '/' . $this->rest_route . '/', [
			'methods'             => 'POST',
			'callback'            => $this->rest_callback,
			'permission_callback' => '__return_true'
		] );
	}

	public function verify() {

		if ( isset( $_POST['ajax_nonce'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				return false;
			}
			if ( ! wp_verify_nonce( $_POST['ajax_nonce'], 'wp_rest' ) ) {
				wp_send_json( [ 'error' => __( 'Signature verification failed', 'userspace' ) . '!' ] );
			}
		} else {
			check_ajax_referer( 'wp_rest' );
		}
	}

}
