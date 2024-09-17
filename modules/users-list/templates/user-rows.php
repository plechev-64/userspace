<?php
/**
 * Html template for displaying the user's card
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/user-rows.php
 * or from a special plugin directory wp-content/userspace/templates/user-rows.php
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

$args = [
	'parent_class' => 'usp-user__ava usps usps__relative',
	'parent_title' => $user->get_username(),
];
?>

<div class="usp-user usp-card usps__grow" data-user-id="<?php echo intval( $user->ID ); ?>">
    <div class="usp-user__top usps usps__nowrap">
        <div class="usp-user__left usps usps__column usps__shrink-0">
			<?php echo $user->get_avatar( 70, $user->get_url(), $args );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>

        <div class="usp-user__right usps usps__column usps__grow">
            <div class="usp-user__general usps usps__jc-between">
				<?php echo wp_kses_post( $user->get_username( $user->get_url(), [ 'class' => 'usp-user__link' ] ) ); ?>
                <div class="usp-user__icons usps__grow"><?php do_action( 'usp_user_rows_icons', $user, $custom_data ); ?></div>
				<?php echo $user->get_action( 'mixed' );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="usp-user__description">
				<?php echo $user->get_description_html();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="usp-user__stats usp-meta-box usps">
				<?php do_action( 'usp_user_stats', $user, $custom_data, $template = 'rows' ); ?>
            </div>
        </div>
    </div>

    <div class="usp-user__bottom">
        <div class="usp-user-fields-before"><?php do_action( 'usp_user_fields_before', $user, $custom_data, $template = 'rows' ); ?></div>

		<?php echo $user->profile_fields()->get_public_fields_values();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <div class="usp-user-fields-after"><?php do_action( 'usp_user_fields_after', $user, $custom_data, $template = 'rows' ); ?></div>
    </div>
</div>
