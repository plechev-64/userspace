<?php
/*
  Template v1.0
 */

$class = 'usp-bar-' . usp_get_option_customizer( 'usp_bar_color', 'dark' );
?>

<div id="usp-bar" class="usp-bar <?php echo $class; ?> usps usps__jc-center usps__line-1" <?php echo usp_bar_customizer_hide(); ?>>
    <div class="usp-bar-wrap usps usps__jc-between usps__grow usps__ai-center usps__relative" <?php echo usp_bar_width(); ?>>
        <div class="usp-bar-left usps usps__ai-center">
			<?php echo usp_get_button( [
				'type'  => 'clear',
				'label' => __( 'Home', 'userspace' ),
				'icon'  => 'fa-home',
				'size'  => 'medium',
				'class' => 'usp-bar__home',
				'href'  => '/',
			] ); ?>
			<?php do_action( 'usp_bar_left_icons' ); ?>
        </div>

        <div class="usp-bar-right usps usps__grow usps__ai-center usps__jc-end">
            <div class="usp-bar__menu-nav usps usps__as-stretch">
                <i class="uspi fa-bars usp-bar__mobile usps usps__ai-center usps__as-stretch usps__hidden"
                   aria-hidden="true"></i>
				<?php wp_nav_menu( [
					'theme_location' => 'usp-bar',
					'container'      => false,
					'menu_id'        => 'usp-bar__nav',
					'menu_class'     => 'usp-bar__nav usps usps__as-stretch',
					'fallback_cb'    => '',
				] ); ?>
            </div>

            <div class="usp-bar__bttns"><?php do_action( 'usp_bar_buttons' ); ?></div>

            <div class="usp-bar__user-nav usp-menu-has-child usps usps__ai-center usps__as-stretch">
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

					$usp_user    = USP()->user( get_current_user_id() );
					$menu_button = $usp_user->get_avatar(
						30,
						'',
						[
							'parent_class' => 'usp-bar-userlink usp-bar-usershow usps usps__ai-center',
							'parent_wrap'  => 'div'
						],
						$usp_user->get_username()
					);

					$menu = new USP_Dropdown_Menu( 'usp_bar_profile_menu', [ 'open_button' => $menu_button ] );

					$menu->add_button( [
						'href'  => $usp_user->get_url( 'profile' ),
						'icon'  => 'fa-address-book',
						'label' => __( 'Profile info', 'userspace' )
					] );

					if ( $usp_user->is_access_console() ) {
						$menu->add_button( [
							'href'  => admin_url(),
							'icon'  => 'fa-external-link-square',
							'label' => __( 'To admin area', 'userspace' ),
						] );
					}

					$menu->add_button( [
						'class' => 'usps__text-right',
						'href'  => wp_logout_url( '/' ),
						'label' => __( 'Log Out', 'userspace' ),
					] );

					echo $menu->get_content();

					?>
				<?php } ?>
            </div>
        </div>
    </div>
</div>
