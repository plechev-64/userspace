<?php global $usp_user_URL, $user_ID; ?>

<div id="recallbar">
    <div class="rcb_left">

		<?php $rcb_menu = wp_nav_menu( array( 'echo' => false, 'theme_location' => 'recallbar', 'container_class' => 'rcb_menu', 'fallback_cb' => '__return_empty_string' ) ); ?>
		<?php if ( $rcb_menu ): ?>
			<div class="rcb_left_menu"><!-- блок rcb_left_menu должен появляться только если есть пункты в меню -->
				<i class="uspi fa-bars" aria-hidden="true"></i>
				<?php echo $rcb_menu; ?>
			</div>
		<?php endif; ?>

        <div class="rcb_icon">
            <a href="/">
                <i class="uspi fa-home" aria-hidden="true"></i>
                <div class="rcb_hiden"><span><?php _e( 'Homepage', 'usp' ); ?></span></div>
            </a>
        </div>

		<?php if ( ! is_user_logged_in() ): ?>

			<div class="rcb_icon">
				<a href="<?php echo usp_get_loginform_url( 'login' ); ?>" class="usp-login">
					<i class="uspi fa-sign-in" aria-hidden="true"></i><span><?php _e( 'Entry', 'usp' ); ?></span>
					<div class="rcb_hiden"><span><?php _e( 'Entry', 'usp' ); ?></span></div>
				</a>
			</div>
			<?php if ( usp_is_register_open() ): ?>
				<div class="rcb_icon">
					<a href="<?php echo usp_get_loginform_url( 'register' ); ?>" class="usp-register">
						<i class="uspi fa-book" aria-hidden="true"></i><span><?php _e( 'Register', 'usp' ); ?></span>
						<div class="rcb_hiden"><span><?php _e( 'Register', 'usp' ); ?></span></div>
					</a>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'usp_bar_left_icons' ); ?>

    </div>

    <div class="rcb_right">
        <div class="rcb_icons">
			<?php do_action( 'usp_bar_print_icons' ); ?>
        </div>

		<?php if ( is_user_logged_in() ): ?>

			<div class="rcb_right_menu">
				<i class="uspi fa-ellipsis-h" aria-hidden="true"></i>
				<a href="<?php echo $usp_user_URL; ?>"><?php echo get_avatar( $user_ID, 36 ); ?></a>
				<div class="pr_sub_menu">
					<?php do_action( 'usp_bar_print_menu' ); ?>
					<div class="rcb_line"><a href="<?php echo wp_logout_url( '/' ); ?>"><i class="uspi fa-sign-out" aria-hidden="true"></i><span><?php _e( 'Exit', 'usp' ); ?></span></a></div>
				</div>
			</div>

		<?php endif; ?>
    </div>
</div>
