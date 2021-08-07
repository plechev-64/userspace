<?php

/**
 * @var USP_User $user
 * @var array $custom_data - data to display
 */

$args = [
	'parent_class' => 'usp-masonry__ava usps usps__jc-center',
	'parent_title' => $user->get_username(),
	'class'        => 'usp-masonry__ava-img usps__radius-50',
];
?>

<div class="usp-card usp-masonry">
    <div class="usp-card__head usp-masonry__head usps__relative">
        <img class="usps__img-reset usps__fit-cover" src="<?php echo $user->get_cover_url( true ); ?>">
		<?php echo $user->get_action( 'mixed' ); ?>
		<?php do_action( 'usp_masonry_ico', $user, $custom_data ); ?>
    </div>

    <div class="usp-card__main usps usps__column">

		<?php echo $user->get_avatar(70, $user->get_url(), $args ); ?>

        <div class="usp-card__center">
			<?php echo $user->get_username( $user->get_url(), [ 'class' => 'usp-card__title' ] ); ?>
            <div class="usp-card__content"><?php do_action( 'usp_masonry_content', $user, $custom_data ); ?></div>
            <div class="usp-masonry__stats usps usps__jc-center usps__line-1"><?php do_action( 'usp_user_stats', $user, $custom_data ); ?></div>
        </div>

        <div class="usp-card__bttns"><?php do_action( 'usp_masonry_buttons', $user, $custom_data ); ?></div>
    </div>
</div>
