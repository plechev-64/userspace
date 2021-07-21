<?php
global $usp_user, $usp_users_set;

$user_id = $usp_user->ID;

$args = [
	'parent_class' => 'usp-masonry__ava usps usps__jc-center',
	'parent_title' => $usp_user->display_name,
	'class'        => 'usp-masonry__ava-img usps__radius-50',
];
?>

<div class="usp-card usp-masonry">
    <div class="usp-card__head usp-masonry__head usps__relative">
        <img class="usps__img-reset usps__fit-cover"
             src="<?php echo usp_get_user_cover( $user_id, $avatar_cover = 1 ); ?>">
		<?php usp_user_action( 2 ); ?>
		<?php do_action( 'usp_masonry_ico' ); ?>
    </div>

    <div class="usp-card__main usps usps__column">
		<?php echo usp_get_avatar( $user_id, 150, usp_get_user_url( $user_id ), $args ); ?>

        <div class="usp-card__center">
			<?php echo usp_user_get_username( false, usp_get_user_url( $user_id ), [ 'class' => 'usp-card__title' ] ); ?>
            <div class="usp-card__content"><?php do_action( 'usp_masonry_content' ); ?></div>
            <div class="usp-masonry__stats usps usps__jc-center usps__line-1"><?php do_action( 'usp_user_stats' ); ?></div>
        </div>

        <div class="usp-card__bttns"><?php do_action( 'usp_masonry_buttons' ); ?></div>
    </div>
</div>
