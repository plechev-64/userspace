<?php
global $usp_user, $usp_users_set;
?>

<div class="usp-card usp-masonry">
    <div class="usp-card__head usp-masonry__head usps__relative">
        <img class="usps__img-reset usps__fit-cover" src="<?php echo usp_get_user_cover( $usp_user->ID, $avatar_cover = 1 ); ?>">
        <?php usp_user_action( 2 ); ?>
        <?php do_action( 'usp_masonry_ico' ); ?>
    </div>

    <div class="usp-card__main usps usps__column">
        <a class="usp-masonry__ava usps usps__jc-center" title="<?php usp_user_name(); ?>" href="<?php usp_user_url(); ?>"><?php usp_user_avatar( 150, [ 'class' => 'usp-masonry__ava-img usps__radius-50' ] ); ?></a>

        <div class="usp-card__center">
            <a class="usp-card__title" href="<?php usp_user_url(); ?>"><?php usp_user_name(); ?></a>
            <div class="usp-card__content"><?php do_action( 'usp_masonry_content' ); ?></div>
            <div class="usp-masonry__stats usps usps__jc-center usps__line-1"><?php do_action( 'usp_user_stats' ); ?></div>
        </div>

        <div class="usp-card__bttns"><?php do_action( 'usp_masonry_buttons' ); ?></div>
    </div>
</div>
