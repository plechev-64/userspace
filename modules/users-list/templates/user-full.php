<?php
/**
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

?>
<div class="usp-user">
    <div class="usp-user__top usps usps__ai-center">
        <div class="usp-user__name"><?php echo $user->get_username(); ?></div>
        <div class="usp-user__meta"><?php do_action( 'usp_user_meta', $user, $custom_data ); ?></div>
    </div>

    <div class="usp-user__description">
		<?php echo $user->get_description_html( [ 'side' => 'top' ] ); ?>
    </div>

    <div class="usp-user__stats usp-meta-box usps">
		<?php echo usp_user_stats_register( $user ); ?>
		<?php echo usp_user_stats_comments( $user ); ?>
		<?php echo usp_user_stats_posts( $user ); ?>
    </div>
    <div class="usp-user__bottom">
        <div class="usp-user-fields-before"><?php do_action( 'usp_user_fields_before', $user, $custom_data ); ?></div>

		<?php echo $user->profile_fields()->get_public_fields_values(); ?>

        <div class="usp-user-fields-after"><?php do_action( 'usp_user_fields_after', $user, $custom_data ); ?></div>
    </div>
</div>
