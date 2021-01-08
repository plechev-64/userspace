<?php
global $usp_user, $usp_users_set;
// если есть вызов в data атрибута comments_count
$uc_count = '';
if ( in_array( 'comments_count', $usp_users_set->data ) ) {
	$uc_count .= '<div class="u_card_half">' . __( 'Comments', 'userspace' ) . '<br/><span>';
	$uc_count .= isset( $usp_user->comments_count ) ? $usp_user->comments_count : '0';
	$uc_count .= '</span></div>';
}
// если есть вызов в data атрибута posts_count
$up_count = '';
if ( in_array( 'posts_count', $usp_users_set->data ) ) {
	$up_count .= '<div class="u_card_half">' . __( 'Posts', 'userspace' ) . '<br/><span>';
	$up_count .= isset( $usp_user->posts_count ) ? $usp_user->posts_count : '0';
	$up_count .= '</span></div>';
}

$style = (isset( $usp_users_set->width )) ? 'style="width:' . $usp_users_set->width . 'px"' : '';
?>
<div class="user-single" <?php echo $style; ?> data-user-id="<?php echo $usp_user->ID; ?>">
    <div class="u_card_top">
		<?php usp_user_rayting(); ?>
		<?php usp_user_action( 2 ); ?>
        <div class="thumb-user">
            <a title="<?php _e( 'to go to office of the user', 'userspace' ); ?>" href="<?php usp_user_url(); ?>">
				<?php usp_user_avatar( 200 ); ?>
            </a>
        </div>
        <div class="u_card_name">
			<?php usp_user_name(); ?>
        </div>
    </div>
    <div class="u_card_bottom">
		<?php
		echo $uc_count;
		echo $up_count;
		?>
    </div>
</div>