<?php
/*
  Template v1.0
 */
?>

<div id="usp-office-profile" class="usp-office-profile">
    <div class="usp-office-top">
        <?php do_action( 'usp_area_top' ); ?>
    </div>
    <div class="usp-office-content">
        <div class="usp-office-center">
            <?php usp_avatar( 200 ); ?>
            <div class="usp-office-title">
                <div class="usp-user-name"><?php usp_username(); ?></div>
                <div class="usp-action"><?php usp_action(); ?></div>
            </div>
        </div>

        <div class="usp-office-bottom">
            <div class="usp-office-bttn-act">
                <?php do_action( 'usp_area_actions' ); ?>
            </div>
            <div class="usp-office-bttn-lite">
                <?php do_action( 'usp_area_counters' ); ?>
            </div>
        </div>
    </div>
</div>

<div id="usp-tabs">
    <?php do_action( 'usp_area_menu' ); ?>

    <?php if ( is_active_sidebar( 'usp_theme_sidebar' ) ) { ?>
        <div class="usp-tabs-wrap">

            <?php do_action( 'usp_area_tabs' ); ?>

            <div class="usp-office-sidebar">
                <?php
                if ( function_exists( 'dynamic_sidebar' ) ) {
                    dynamic_sidebar( 'usp_theme_sidebar' );
                }
                ?>
            </div>
        </div>
    <?php } else { ?>
        <?php do_action( 'usp_area_tabs' ); ?>
    <?php } ?>
</div>

