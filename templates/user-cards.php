<?php
global $usp_user, $usp_users_set;

// if get shortcode attr comments_count
$uc_count = '';
if ( in_array( 'comments_count', $usp_users_set->data ) ) {
    $uc_count .= '<div class="usp-user__half usps usps__column usps__ai-center">';
    $uc_count .= '<div>' . (isset( $usp_user->comments_count ) ? $usp_user->comments_count : '0') . '</div>';
    $uc_count .= '<div>' . __( 'Comments', 'userspace' ) . '</div>';
    $uc_count .= '</div>';
}
// if get shortcode attr posts_count
$up_count = '';
if ( in_array( 'posts_count', $usp_users_set->data ) ) {
    $up_count .= '<div class="usp-user__half usps usps__column usps__ai-center">';
    $up_count .= '<div>' . (isset( $usp_user->posts_count ) ? $usp_user->posts_count : '0') . '</div>';
    $up_count .= '<div>' . __( 'Posts', 'userspace' ) . '</div>';
    $up_count .= '</div>';
}

$style = (isset( $usp_users_set->width )) ? 'style="width:' . $usp_users_set->width . 'px"' : '';
?>

<div class="usp-user usps usps__column usps__grow" <?php echo $style; ?> data-user-id="<?php echo $usp_user->ID; ?>">
    <div class="usp-user__picture usps usps__relative usps__grow">
        <a class="usp-user__ava usps" title="<?php _e( 'Go to the user profile page', 'userspace' ); ?>" href="<?php usp_user_url(); ?>">
            <?php usp_user_avatar( 200 ); ?>
            <?php usp_user_action( 2 ); ?>
        </a>
        <?php usp_user_rayting(); ?>
        <div class="usp-user__name usps usps__jc-center"><?php usp_user_name(); ?></div>
    </div>
    <div class="usp-user__count">
        <?php
        echo $uc_count;
        echo $up_count;
        ?>
    </div>
</div>