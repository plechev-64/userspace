<?php global $usp_user, $usp_users_set; ?>
<div class="user-single" data-user-id="<?php echo $usp_user->ID; ?>">
    <div class="thumb-user">
        <a title="<?php usp_user_name(); ?>" href="<?php usp_user_url(); ?>">
			<?php usp_user_avatar( 70 ); ?>
			<?php usp_user_action(); ?>
        </a>
		<?php usp_user_rayting(); ?>
    </div>
</div>