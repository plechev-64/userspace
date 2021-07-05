<?php

class USP_User {

	private $id;
	private $time_update;

	function __construct( $user_id = false ) {
		global $user_ID;

		if ( ! $user_id ) {
			$user_id = $user_ID;
		}

		$this->id = $user_id;
	}

	function get_url() {

		$officeUrl = get_permalink( usp_get_option( 'account_page' ) );

		if ( '' == get_site_option( 'permalink_structure' ) ) {

			$officeUrl = add_query_arg(
				array(
					'user' => $this->id
				), $officeUrl );

		}else{
			$userData = get_userdata( $this->id );
			$officeUrl = untrailingslashit( $officeUrl ) . '/' . $userData->user_nicename;
		}

		return $officeUrl;
	}

	function get_last_action() {

	}

	function is_online() {

	}

	function get_action_status() {

	}

	function get_offline_diff() {

	}

	function get_action_html() {

	}

	function is_role( $role ) {

	}

	function is_access_console() {

	}

	function get_cover_url() {

	}

	function update_activity() {
		global $wpdb;

		if ( ! $this->id ) {
			return false;
		}

		$last_action = usp_get_useraction( usp_get_time_user_action( $this->id ) );

		if ( $last_action ) {

			$time = current_time( 'mysql' );

			$res = $wpdb->update(
				USP_PREF . 'users_actions', array( 'date_action' => $time ), array( 'user_id' => $this->id )
			);

			if ( ! isset( $res ) || $res == 0 ) {
				$act_user = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(date_action) FROM " . USP_PREF . "users_actions WHERE user_id ='%d'", $this->id ) );
				if ( $act_user == 0 ) {
					$wpdb->insert(
						USP_PREF . 'users_actions', array( 'user_id' => $this->id, 'date_action' => $time )
					);
				}
				if ( $act_user > 1 ) {
					usp_delete_user_action( $this->id );
				}
			}
		}

		do_action( 'usp_update_timeaction_user' );

	}

}
