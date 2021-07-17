<?php //print_r($user); ?>
<div class="usp-card usp-user usps__grow" data-user-id="<?php echo $user->ID; ?>">
    <div class="usp-user__top usps usps__nowrap">
        <div class="usp-user__left usps usps__column usps__shrink-0">
            <?php echo usp_get_avatar( $user->ID, 70, $url, $args ); ?>
            <?php usp_user_rayting(); ?>
        </div>

        <div class="usp-user__right usps usps__column usps__grow">
            <div class="usp-user__general usps usps__jc-between">
                <a class="usp-user__link" href="<?php echo $url; ?>"><?php echo usp_get_username(); ?></a>
                <div class="usp-user__icons usps__grow"><?php do_action( 'usp_user_icons' ); ?></div>
                <?php usp_user_action( 2 ); ?>
            </div>

            <?php echo usp_get_user_description(); ?>

            <div class="usp-user__stats usps"><?php do_action( 'usp_user_stats' ); ?></div>
        </div>
    </div>

    <div class="usp-user__bottom">
        <div class="usp-user__fields-before"><?php do_action( 'usp_user_fields_before' ); ?></div>

        <div class="usp-user__fields usps usps__column"><?php echo usp_get_user_custom_fields(); ?></div>

        <div class="usp-user__fields-after"><?php do_action( 'usp_user_fields_after' ); ?></div>
    </div>
</div>
