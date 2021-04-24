<?php
/*
  Template v1.0
 */

global $usp_user_URL, $user_ID;

$class = 'usp-bar-' . usp_get_option( 'usp_bar_color', 'dark' );
$width = usp_get_option( 'usp_bar_width' ) ? 'style="max-width:' . usp_get_option( 'usp_bar_width' ) . 'px;"' : 'style="max-width:calc(100% - 24px)"';

$user = get_userdata( $user_ID );
?>

<div id="usp-bar" class="usp-bar <?php echo $class; ?> usps usps__jc-center usps__line-1">
    <div class="usp-bar-wrap usps usps__jc-between usps__grow usps__ai-center usps__relative" <?php echo $width; ?>>
        <div class="usp-bar-left usps usps__ai-center">
            <?php echo usp_get_button( [ 'type' => 'clear', 'label' => __( 'Home', 'userspace' ), 'icon' => 'fa-home', 'size' => 'medium', 'href' => '/' ] ); ?>
            <?php do_action( 'usp_bar_left_icons' ); ?>
        </div>

        <div class="usp-bar-right usps usps__grow usps__ai-center usps__jc-end">
            <?php wp_nav_menu( array( 'theme_location' => 'usp-bar', 'container' => false, 'menu_id' => 'rcb_menu', 'menu_class' => 'rcb_menu usps usps__as-stretch', 'fallback_cb' => '' ) ); ?>

            <div class="rcb_icons"><?php do_action( 'usp_bar_print_icons' ); ?></div>

            <div class="rcb_right_menu usp-menu-has-child usps usps__ai-center usps__as-stretch">
                <?php if ( ! is_user_logged_in() ) { ?>
                    <?php echo usp_get_button( [ 'type' => 'clear', 'label' => __( 'Sign in', 'userspace' ), 'icon' => 'fa-sign-in', 'size' => 'medium', 'href' => usp_get_loginform_url( 'login' ), 'class' => 'usp-entry-bttn usp-login usps__as-stretch' ] ); ?>

                    <?php if ( usp_is_register_open() ) { ?>
                        <span class="usp-bar-or">or</span>
                        <?php echo usp_get_button( [ 'type' => 'clear', 'label' => __( 'Register', 'userspace' ), 'href' => usp_get_loginform_url( 'register' ), 'class' => 'usp-entry-bttn usp-register usps__as-stretch' ] ); ?>
                    <?php } ?>

                <?php } else { ?>
                    <a class="usp-bar-userlink usp-bar-usershow usps usps__ai-center" href="<?php echo $usp_user_URL; ?>">
                        <?php echo get_avatar( $user_ID, 40, false, false, [ 'class' => 'usp-profile-ava usps__img-reset' ] ); ?>
                        <span><?php echo $user->get( 'user_firstname' ); ?></span>
                    </a>

                    <div class="usp-sub-menu">
                        <?php do_action( 'usp_bar_before_print_menu' ); ?>
                        <div class="usp-bar-usertabs usps usps__column">
                            <?php do_action( 'usp_bar_print_menu' ); ?>
                        </div>
                        <?php echo usp_get_button( [ 'type' => 'clear', 'fullwidth' => 1, 'class' => 'rcb_line usp-bar-logout', 'label' => __( 'Log Out', 'userspace' ), 'href' => wp_logout_url( '/' ) ] ); ?>
                    </div>

                    <i class="uspi fa-angle-down usp-bar-usershow usps usps__ai-center usps__as-stretch" aria-hidden="true"></i>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
