<?php
/*
  Template v1.0
 */

global $usp_user_URL, $user_ID;

$class = 'usp-bar-' . usp_get_option( 'usp_bar_color', 'dark' );
$width = usp_get_option( 'usp_bar_width' ) ? 'style="max-width:' . usp_get_option( 'usp_bar_width' ) . 'px;"' : '';

$usp_bar_menu = wp_nav_menu( array( 'echo' => false, 'theme_location' => 'usp-bar', 'container_class' => 'rcb_menu', 'fallback_cb' => '' ) );
?>

<div id="usp-bar" class="usp-bar <?php echo $class; ?> usps usps__jc-center usps__line-1">
    <div class="usp-bar-wrap usps usps__jc-between usps__grow usps__ai-center usps__relative" <?php echo $width; ?>>
        <div class="usp-bar-left">
            <?php echo usp_get_button( [ 'type' => 'clear', 'label' => __( 'Home', 'userspace' ), 'icon' => 'fa-home', 'size' => 'medium', 'href' => '/' ] ); ?>
            <?php do_action( 'usp_bar_left_icons' ); ?>
        </div>

        <div class="usp-bar-right">
            <?php if ( $usp_bar_menu ): ?>
                <div class="rcb_left_menu">
                    <i class="uspi fa-bars" aria-hidden="true"></i>
                    <?php echo $usp_bar_menu; ?>
                </div>
            <?php endif; ?>
            <div class="rcb_icons">
                <?php do_action( 'usp_bar_print_icons' ); ?>
            </div>

            <?php if ( ! is_user_logged_in() ) { ?>

                <div class="rcb_icon">
                    <a href="<?php echo usp_get_loginform_url( 'login' ); ?>" class="usp-login">
                        <i class="uspi fa-sign-in" aria-hidden="true"></i><span><?php _e( 'Sign in', 'userspace' ); ?></span>
                        <span><?php _e( 'Sign in', 'userspace' ); ?></span>
                    </a>
                </div>
                <?php if ( usp_is_register_open() ) { ?>
                    <div class="rcb_icon">
                        <a href="<?php echo usp_get_loginform_url( 'register' ); ?>" class="usp-register">
                            <i class="uspi fa-book" aria-hidden="true"></i><span><?php _e( 'Register', 'userspace' ); ?></span>
                            <span><?php _e( 'Register', 'userspace' ); ?></span>
                        </a>
                    </div>
                <?php } ?>

            <?php } else { ?>

                <div class="rcb_right_menu">
                    <i class="uspi fa-horizontal-ellipsis" aria-hidden="true"></i>
                    <a href="<?php echo $usp_user_URL; ?>"><?php echo get_avatar( $user_ID, 36, false, false, [ 'class' => 'usp-profile-ava usps__img-reset' ] ); ?></a>
                    <div class="pr_sub_menu">
                        <?php do_action( 'usp_bar_print_menu' ); ?>
                        <div class="rcb_line"><a href="<?php echo wp_logout_url( '/' ); ?>"><i class="uspi fa-sign-out" aria-hidden="true"></i><span><?php _e( 'Exit', 'userspace' ); ?></span></a></div>
                    </div>
                </div>

            <?php } ?>
        </div>
    </div>
</div>
