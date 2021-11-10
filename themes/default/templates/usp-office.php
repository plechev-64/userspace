<?php
/**
 * User account page.
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/usp-office.php
 * or from a special plugin directory wp-content/userspace/templates/usp-office.php
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
 */

defined( 'ABSPATH' ) || exit;

$args = [
	'parent_wrap'  => 'div',
	'parent_id'    => 'usp-avatar',
	'class'        => 'usps__fit-cover',
	'parent_class' => 'usp-office-ava usps__relative',
];

/**
 * Filter allows add buttons in avatar area.
 *
 * @param   $bttns  string  HTML of buttons.
 *
 * @since   1.0.0
 */
$html = '<div class="usp-ava-bttns">' . apply_filters( 'usp_avatar_bttns', '' ) . '</div>';

$office_owner = USP()->office()->owner();
$has_sidebar  = ( is_active_sidebar( 'usp_theme_sidebar' ) ) ? 'usp-profile__has-sidebar' : '';
?>

<div id="usp-office-profile"
     class="usp-office-profile usps usps__column usps__nowrap usps__relative">
    <div class="usp-office-top usps usps__jc-end">
		<?php
		/**
		 * Fires on the account page - from the top, in the cover area.
		 *
		 * @since   1.0.0
		 */
		do_action( 'usp_area_top' ); ?>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo usp_get_button( [
			'size'    => 'medium',
			'class'   => 'usp-office-shift',
			'icon'    => 'fa-horizontal-sliders',
			'onclick' => 'usp_office_shift(this);return false;',
		] ); ?>
    </div>
    <div class="usp-office-card usps usps__nowrap usps__relative">
        <div class="usp-office-left usps usps__column">
			<?php echo usp_get_avatar( $office_owner->ID, 200, false, $args, $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div class="usp-under-ava"><?php
				/**
				 * Fires on the account page - at the bottom of the avatar.
				 *
				 * @since   1.0.0
				 */
				do_action( 'usp_area_under_ava' ); ?></div>
        </div>

        <div class="usp-office-right usps usps__column usps__grow usps__jc-between">
            <div class="usp-office-usermeta usps usps__column">
                <div class="usp-office-title usps">
                    <div class="usp-user-name">
                        <div><?php echo esc_html( $office_owner->get_username() ); ?></div>
                    </div>
                    <div class="usp-action"><?php echo $office_owner->get_action_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                </div>
                <div class="usp-user-icons"><?php
					/**
					 * Fires on the account page - in the name area.
					 *
					 * @since   1.0.0
					 */
					do_action( 'usp_area_icons' ); ?></div>
            </div>

            <div class="usp-office-middle usps usps__column usps__grow">
                <div class="usp-office-bttn-act">
					<?php echo USP()->tabs()->get_menu( 'actions' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
                <div class="usp-office-box"><?php
					/**
					 * Fires on the account page - under actions area.
					 *
					 * @since   1.0.0
					 */
					do_action( 'usp_area_box' ); ?></div>
            </div>

            <div class="usp-office-bottom">
                <div class="usp-office-bttn-lite usps usps__jc-end">
					<?php echo USP()->tabs()->get_menu( 'counters' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="usp-tabs" class="usp-tab-area usps usps__nowrap usps__relative">
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo USP()->tabs()->get_menu( 'menu', [
		'class' => usp_get_option( 'usp_office_tab_type', 1 ) ? false : 'usps__column',
	] ); ?>

    <div class="usp-profile-content <?php echo esc_html( $has_sidebar ); ?> usps usps__nowrap usps__grow">
		<?php $current = USP()->tabs()->current();
		if ( $current ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $current->get_content();
		} ?>

		<?php if ( function_exists( 'dynamic_sidebar' ) && is_active_sidebar( 'usp_theme_sidebar' ) ) { ?>
            <div class="usp-profile__sidebar">
				<?php dynamic_sidebar( 'usp_theme_sidebar' ); ?>
            </div>
		<?php } ?>
    </div>
</div>
