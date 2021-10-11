<?php
/**
 * Top panel - UserSpace bar
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/usp-bar.php
 * or from a special plugin directory wp-content/userspace/templates/usp-bar.php
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

$color      = usp_get_option_customizer( 'usp_bar_color', 'dark' );
$class      = 'usp-bar-' . $color;
$menu_color = ( 'white' === $color ) ? 'white' : 'none';
?>

<div id="usp-bar" class="usp-bar <?php echo sanitize_html_class( $class ); ?> usps usps__jc-center usps__line-1" <?php echo usp_bar_customizer_hide(); ?>>
    <div class="usp-bar-wrap usps usps__jc-between usps__grow usps__ai-center usps__relative" <?php echo usp_bar_width(); ?>>
        <div class="usp-bar-left usps usps__ai-center">
			<?php echo usp_get_button( [
				'type'  => 'clear',
				'label' => __( 'Home', 'userspace' ),
				'icon'  => 'fa-home',
				'class' => 'usp-bar__home',
				'href'  => '/',
			] ); ?>
			<?php do_action( 'usp_bar_left_icons' ); ?>
        </div>

        <div class="usp-bar-right usps usps__grow usps__ai-center usps__jc-end">

			<?php echo get_test_dropdown_menu( $menu_color ); ?>

            <div class="usp-bar__bttns"><?php do_action( 'usp_bar_buttons' ); ?></div>

            <div class="usp-bar__user-nav usps usps__ai-center usps__as-stretch">
				<?php if ( ! is_user_logged_in() ) { ?>
					<?php echo usp_get_button( [
						'type'  => 'clear',
						'label' => __( 'Sign in', 'userspace' ),
						'icon'  => 'fa-sign-in',
						'href'  => usp_get_loginform_url( 'login' ),
						'class' => 'usp-entry-bttn usp-login usps__as-stretch',
					] ); ?>

					<?php if ( usp_is_register_open() ) { ?>
                        <span class="usp-bar-or">or</span>
						<?php echo usp_get_button( [
							'type'  => 'clear',
							'label' => __( 'Register', 'userspace' ),
							'href'  => usp_get_loginform_url( 'register' ),
							'class' => 'usp-entry-bttn usp-register usps__as-stretch',
						] ); ?>
					<?php } ?>

				<?php } else { ?>
					<?php
					$usp_user = USP()->user( get_current_user_id() );

					$menu_button = usp_get_button( [
						'type'       => 'clear',
						'label'      => $usp_user->get_username(),
						'class'      => 'usp-bar-userlink',
						'icon'       => 'fa-angle-down',
						'icon_align' => 'right',
						'avatar'     => $usp_user->get_avatar( 30 ),
					] );

					$menu = new USP_Dropdown_Menu( 'usp_bar_profile_menu', [ 'open_button' => $menu_button, 'style' => $menu_color ] );

					$menu->add_button(
						[
							'class' => 'usp-bar-profile__in-account',
							'href'  => $usp_user->get_url(),
							'icon'  => 'fa-user',
							'label' => __( 'Go to personal account', 'userspace' )
						],
						[ 'order' => 10 ]
					);

					$menu->add_button(
						[
							'class' => 'usp-bar-profile__edit',
							'href'  => $usp_user->get_url( 'profile', 'edit' ),
							'icon'  => 'fa-user-cog',
							'label' => __( 'Edit profile', 'userspace' )
						],
						[ 'order' => 20 ]
					);

					if ( $usp_user->is_access_console() ) {
						$menu->add_button(
							[
								'class' => 'usp-bar-profile__in-admin',
								'href'  => admin_url(),
								'icon'  => 'fa-external-link-square',
								'label' => __( 'To admin area', 'userspace' ),
							],
							[ 'order' => 80 ]
						);
					}

					$menu->add_button(
						[
							'class' => 'usp-bar-profile__logout usps__jc-end',
							'href'  => wp_logout_url( '/' ),
							'label' => __( 'Log Out', 'userspace' ),
						],
						[ 'order' => 100 ]
					);

					echo $menu->get_content();

					?>
				<?php } ?>
            </div>
        </div>
    </div>
</div>
