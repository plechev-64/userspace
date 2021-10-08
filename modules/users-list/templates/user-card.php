<?php
/**
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

$args = [
	'parent_class' => 'usp-user__ava usps usps__relative',
	'parent_title' => $user->get_username(),
];

?>
<div class="usp-user usp-card usps__grow" data-user-id="<?php echo $user->ID; ?>">
    <div class="usp-user__top usps usps__nowrap">
        <div class="usp-user__left usps usps__column usps__shrink-0">
			<?php echo $user->get_avatar( 70, $user->get_url(), $args ); ?>
        </div>

        <div class="usp-user__right usps usps__column usps__grow usps__ml-6">
            <div class="usp-user__general usps usps__jc-between">
				<?php echo $user->get_username( $user->get_url(), [ 'class' => 'usp-user__link' ] ); ?>
                <div class="usp-user__icons usps__grow"><?php do_action( 'usp_user_icons', $user, $custom_data ); ?></div>
				<?php echo $user->get_action( 'mixed' ); ?>
            </div>
            <div class="usp-user__description">
				<?php echo $user->get_description_html( [ 'class' => 'usps__mt-6' ] ); ?>
            </div>
            <div class="usp-user__stats usp-meta-box usps">
				<?php do_action( 'usp_user_stats', $user, $custom_data ); ?>
            </div>
        </div>
    </div>

    <div class="usp-user__bottom">
        <div class="usp-user-fields-before"><?php do_action( 'usp_user_fields_before', $user, $custom_data ); ?></div>

		<?php echo $user->profile_fields()->get_public_fields_values(); ?>

        <div class="usp-user-fields-after"><?php do_action( 'usp_user_fields_after', $user, $custom_data ); ?></div>
    </div>
</div>
