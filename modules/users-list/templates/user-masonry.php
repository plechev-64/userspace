<?php
/**
 * Html template for displaying the user's card
 *
 * This template can be overridden by copying it to yourtheme/userspace/templates/user-masonry.php
 * or from a special plugin directory wp-content/userspace/templates/user-masonry.php
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

$args = [
	'parent_class' => 'usp-masonry__ava usps usps__jc-center',
	'parent_title' => $user->get_username(),
	'class'        => 'usp-masonry__ava-img usps__radius-50',
];
?>

<div class="usp-user usp-card usp-masonry">
    <div class="usp-card__head usp-masonry__head usps__relative">
        <img class="usps__img-reset usps__fit-cover" src="<?php echo esc_url( $user->get_cover_url( true ) ); ?>">
		<?php echo $user->get_action( 'mixed' );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php do_action( 'usp_user_masonry_icons', $user, $custom_data ); ?>
    </div>

    <div class="usp-card__main usps usps__column">

		<?php echo $user->get_avatar( 110, $user->get_url(), $args );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <div class="usp-card__center">
			<?php echo wp_kses_post( $user->get_username( $user->get_url(), [ 'class' => 'usp-card__title' ] ) ); ?>
            <div class="usp-card__content"><?php do_action( 'usp_user_masonry_content', $user, $custom_data ); ?></div>
            <div class="usp-masonry__stats usps usps__jc-center usps__line-1">
				<?php do_action( 'usp_user_stats', $user, $custom_data, $template = 'masonry' ); ?>
            </div>
        </div>

        <div class="usp-card__bttns"><?php do_action( 'usp_user_masonry_buttons', $user, $custom_data ); ?></div>
    </div>
</div>
