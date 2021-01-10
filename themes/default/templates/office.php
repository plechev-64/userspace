<?php
/*
  Template v1.0
 */
?>

<div id="lk-conteyner">
    <div class="cab_header">
        <?php do_action( 'usp_area_top' ); ?>
    </div>
    <div class="cab_content">
        <div class="cab_center">
            <div class="lk-sidebar">
                <?php usp_avatar( 200 ); ?>
            </div>
            <div class="cab_title">
                <h2><?php usp_username(); ?></h2>
                <div class="usp-action"><?php usp_action(); ?></div>
            </div>
        </div>

        <div class="cab_footer">
            <div class="cab_bttn">
                <?php do_action( 'usp_area_actions' ); ?>
            </div>
            <div class="cab_bttn_lite">
                <?php do_action( 'usp_area_counters' ); ?>
            </div>
        </div>
    </div>
</div>

<div id="usp-tabs">
    <?php do_action( 'usp_area_menu' ); ?>

    <?php if ( is_active_sidebar( 'usp_cab_sidebar' ) ) { ?>
        <div class="cab_content_blk">

            <?php do_action( 'usp_area_tabs' ); ?>

            <div class="cab_sidebar">
                <?php
                if ( function_exists( 'dynamic_sidebar' ) ) {
                    dynamic_sidebar( 'usp_cab_sidebar' );
                }
                ?>
            </div>
        </div>
    <?php } else { ?>
        <?php do_action( 'usp_area_tabs' ); ?>
    <?php } ?>
</div>

