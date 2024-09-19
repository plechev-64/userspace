<?php
/**
 * Html template for displaying the user card in modal window.
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/user-modal.php
 * or from a special plugin directory wp-content/userspace/templates/user-modal.php
 *
 * HOWEVER, on occasion UserSpace will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.user-space.com/document/template-structure/
 *
 * @version 1.0.0
 *
 * @var User $user
 * @var array $custom_data - data to display
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="usp-user-modal__bottom" class="usp-user-modal__bottom usps usps__nowrap usps__column usps__grow">
    <div class="usp-user-modal__ava usps__relative">
		<?php echo $user->get_avatar( 450, false, [ 'class' => 'usp-user-modal__ava-img' ] );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php $avatar = get_user_meta( $user->ID, 'usp_avatar', 1 );

		if ( $avatar ) {
			$url_avatar = get_avatar_url( $user->ID, [ 'size' => 1000 ] ); ?>
            <a href="#" class="usp-user-modal__ava-zoom usps__hidden" data-zoom="<?php echo esc_url( $url_avatar ); ?>" onclick="usp_zoom_avatar(this);return false;">
                <i class="uspi fa-search-plus usps usps__column usps__jc-center usps__grow"></i>
            </a>
		<?php } ?>

    </div>

    <div class="usp-user-modal__content">
		<?php echo $user->get_description_html( [ 'side' => 'top', 'class' => 'usp-user-modal__description' ] );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <div class="usp-user-modal__extra"><?php do_action( 'usp_user_modal_extra', $user, $custom_data ); ?></div>

        <div class="usp-user-modal__stats usp-meta-box usps">
			<?php do_action( 'usp_user_stats', $user, $custom_data, $template = 'modal' ); ?>
        </div>
        <div class="usp-user-modal__fields">
            <div class="usp-user-fields-before"><?php do_action( 'usp_user_fields_before', $user, $custom_data, $template = 'modal' ); ?></div>

			<?php echo $user->profile_fields()->get_public_fields_values();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="usp-user-fields-after"><?php do_action( 'usp_user_fields_after', $user, $custom_data, $template = 'modal' ); ?></div>
        </div>
    </div>
</div>
