<?php
/**
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

?>
<div id="usp-user-modal__bottom" class="usp-user-modal__bottom usps usps__nowrap usps__column usps__grow">
    <div class="usp-user-modal__ava usps__relative">
		<?php echo $user->get_avatar( 450, false, [ 'class' => 'usp-user-modal__ava-img' ] ); ?>

		<?php $avatar = get_user_meta( $user->ID, 'usp_avatar', 1 );

		if ( $avatar ) {
			$url_avatar = get_avatar_url( $user->ID, [ 'size' => 1000 ] ); ?>
            <a href="#" class="usp-user-modal__ava-zoom usps__hidden" data-zoom="<?php echo $url_avatar; ?>" onclick="usp_zoom_avatar(this);return false;">
                <i class="uspi fa-search-plus usps usps__column usps__jc-center usps__grow"></i>
            </a>
		<?php } ?>

    </div>

    <div class="usp-user-modal__content">
		<?php echo $user->get_description_html( [ 'side' => 'top', 'class' => 'usp-user-modal__description' ] ); ?>

        <div class="usp-user-modal__extra"><?php do_action( 'usp_user_modal_extra', $user ); ?></div>

        <div class="usp-user-modal__stats usp-meta-box usps">
			<?php echo usp_user_stats_register( $user ); ?>
			<?php echo usp_user_stats_comments( $user ); ?>
			<?php echo usp_user_stats_posts( $user ); ?>
        </div>
        <div class="usp-user-modal__fields">
            <div class="usp-user-fields-before"><?php do_action( 'usp_user_modal_fields_before', $user ); ?></div>

			<?php echo $user->profile_fields()->get_public_fields_values(); ?>

            <div class="usp-user-fields-after"><?php do_action( 'usp_user_modal_fields_after', $user ); ?></div>
        </div>
    </div>
</div>
