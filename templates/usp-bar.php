<?php
/*
  Template v1.0
 */

global $user_ID;

$class	 = 'usp-bar-' . usp_get_option( 'usp_bar_color', 'dark' );
$width	 = usp_get_option( 'usp_bar_width' ) ? 'style="max-width:' . usp_get_option( 'usp_bar_width' ) . 'px;"' : 'style="max-width:calc(100% - 24px)"';

$user_name = '<span>' . usp_get_username( $user_ID ) . '</span>';
?>

<div id="usp-bar" class="usp-bar <?php echo $class; ?> usps usps__jc-center usps__line-1">
    <div class="usp-bar-wrap usps usps__jc-between usps__grow usps__ai-center usps__relative" <?php echo $width; ?>>
        <div class="usp-bar-left usps usps__ai-center">
			<?php
			echo usp_get_button( [
				'type'	 => 'clear',
				'label'	 => __( 'Home', 'userspace' ),
				'icon'	 => 'fa-home',
				'size'	 => 'medium',
				'href'	 => '/'
			] );
			?>
<?php do_action( 'usp_bar_left_icons' ); ?>
        </div>

        <div class="usp-bar-right usps usps__grow usps__ai-center usps__jc-end">
            <div class="usp-bar__menu-nav usps usps__as-stretch">
                <i class="uspi fa-bars usp-bar__mobile usps usps__ai-center usps__as-stretch usps__hidden"
                   aria-hidden="true"></i>
				   <?php
				   wp_nav_menu( array(
					   'theme_location' => 'usp-bar',
					   'container'		 => false,
					   'menu_id'		 => 'usp-bar__nav',
					   'menu_class'	 => 'usp-bar__nav usps usps__as-stretch',
					   'fallback_cb'	 => ''
				   ) );
				   ?>
            </div>

            <div class="usp-bar__bttns"><?php do_action( 'usp_bar_buttons' ); ?></div>

            <div class="usp-bar__user-nav usp-menu-has-child usps usps__ai-center usps__as-stretch">
				<?php if ( ! is_user_logged_in() ) { ?>
					<?php
					echo usp_get_button( [
						'type'	 => 'clear',
						'label'	 => __( 'Sign in', 'userspace' ),
						'icon'	 => 'fa-sign-in',
						'size'	 => 'medium',
						'href'	 => usp_get_loginform_url( 'login' ),
						'class'	 => 'usp-entry-bttn usp-login usps__as-stretch'
					] );
					?>

					<?php if ( usp_is_register_open() ) { ?>
						<span class="usp-bar-or">or</span>
						<?php
						echo usp_get_button( [
							'type'	 => 'clear',
							'label'	 => __( 'Register', 'userspace' ),
							'href'	 => usp_get_loginform_url( 'register' ),
							'class'	 => 'usp-entry-bttn usp-register usps__as-stretch'
						] );
						?>
							<?php } ?>

						<?php } else { ?>
							<?php echo usp_get_avatar( $user_ID, 40, USP()->user( get_current_user_id() )->get_url(), [ 'parent_class' => 'usp-bar-userlink usp-bar-usershow usps usps__ai-center' ], $user_name ); ?>

					<div class="usp-sub-menu">
							<?php do_action( 'usp_bar_before_print_menu' ); ?>
						<div class="usp-bar-usertabs usp-wrap__wiget usps usps__column">
	<?php do_action( 'usp_bar_profile_menu_buttons' ); ?>
	<?php
	echo usp_get_button( [
		'type'	 => 'clear',
		'size'	 => 'medium',
		'class'	 => 'usp-bar-profile__logout usps__text-right',
		'label'	 => __( 'Log Out', 'userspace' ),
		'href'	 => wp_logout_url( '/' )
	] );
	?>
						</div>
					</div>

					<i class="uspi fa-angle-down usp-bar-usershow usps usps__ai-center usps__as-stretch"
					   aria-hidden="true"></i>
<?php } ?>
            </div>
        </div>
    </div>
</div>
