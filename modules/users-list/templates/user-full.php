<?php
/**
 * Html template for displaying the user's card
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/user-full.php
 * or from a special plugin directory wp-content/userspace/templates/user-full.php
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
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="usp-user">
    <div class="usp-user__top usps usps__ai-center">
        <div class="usp-user__name"><?php echo esc_html( $user->get_username() ); ?></div>
        <div class="usp-user__meta"><?php do_action( 'usp_user_full_meta', $user, $custom_data ); ?></div>
    </div>

    <div class="usp-user__description">
		<?php echo $user->get_description_html( [ 'side' => 'top' ] );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>

    <div class="usp-user__stats usp-meta-box usps">
		<?php do_action( 'usp_user_stats', $user, $custom_data, $template = 'full' ); ?>
    </div>
    <div class="usp-user__bottom">
        <div class="usp-user-fields-before"><?php do_action( 'usp_user_fields_before', $user, $custom_data, $template = 'full' ); ?></div>

		<?php echo $user->profile_fields()->get_public_fields_values();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <div class="usp-user-fields-after"><?php do_action( 'usp_user_fields_after', $user, $custom_data, $template = 'full' ); ?></div>
    </div>
</div>
