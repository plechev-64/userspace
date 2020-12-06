<?php global $usp_user, $usp_users_set; ?>
<div class="user-single" data-user-id="<?php echo $usp_user->ID; ?>">
    <div class="userlist_top">
		<?php usp_user_action( 2 ); ?>
        <h3 class="user-name">
            <a href="<?php usp_user_url(); ?>"><?php usp_user_name(); ?></a>
        </h3>
    </div>

    <div class="userlist_cntr">
        <div class="thumb-user">
            <a title="<?php usp_user_name(); ?>" href="<?php usp_user_url(); ?>">
				<?php usp_user_avatar( 70 ); ?>
            </a>
			<?php usp_user_rayting(); ?>
        </div>

        <div class="user-content-usp">
			<?php usp_user_description(); ?>
        </div>
    </div>
</div>