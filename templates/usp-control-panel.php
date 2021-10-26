<?php
/**
 * Html template for Control panel
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/usp-control-panel.php
 * or from a special plugin directory wp-content/userspace/templates/usp-control-panel.php
 *
 * HOWEVER, on occasion UserSpace will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.user-space.com/document/template-structure/
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// use loginform module
if ( ! is_user_logged_in() && ! usp_get_option( 'usp_login_form' ) ) {
	usp_dialog_scripts();
}
?>

<div class="usp-user-widget usps">
	<?php if ( is_user_logged_in() ) { ?>
        <div class="usp-user-widget__left">
			<?php echo usp_get_avatar( get_current_user_id(), 100, false, [ 'class' => 'usps__fit-cover' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php do_action( 'usp_control_panel_left' ); ?>
        </div>

		<?php $buttons[] = [
			'label' => __( 'My account', 'userspace' ),
			'icon'  => 'fa-home',
			'class' => 'usp-user-widget__account',
			'href'  => usp_user_get_url()
		];

		$buttons[] = [
			'label' => __( 'Exit', 'userspace' ),
			'icon'  => 'fa-external-link',
			'class' => 'usp-user-widget__exit',
			'href'  => wp_logout_url( home_url() ),
		];
	} else {
		$buttons[] = [
			'label'   => __( 'Sign in', 'userspace' ),
			'icon'    => 'fa-sign-in',
			'class'   => 'usp-user-widget__login usp-entry-bttn',
			'onclick' => usp_get_option( 'usp_login_form' ) ? null : 'USP.loginform.call("login");return false;',
			'href'    => usp_get_loginform_url( 'login' ),
		];

		$buttons[] = [
			'label'   => __( 'Register', 'userspace' ),
			'icon'    => 'fa-book',
			'class'   => 'usp-user-widget__register usp-entry-bttn',
			'onclick' => usp_get_option( 'usp_login_form' ) ? null : 'USP.loginform.call("register");return false;',
			'href'    => usp_get_loginform_url( 'register' ),
		];
	}

	$all_buttons = apply_filters( 'usp_control_panel_buttons', $buttons );

	if ( $all_buttons ) : ?>
        <div class="usp-user-widget__right usps usps__column">
			<?php do_action( 'usp_control_panel_right_before' ); ?>
			<?php foreach ( $all_buttons as $button ) {
				echo usp_get_button( $button ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} ?>
			<?php do_action( 'usp_control_panel_right_after' ); ?>
        </div>
	<?php endif; ?>

</div>
