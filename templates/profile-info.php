<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="usp-info">
    <div class="usp-info__top usps usps__ai-center">
        <div class="usp-info__name"><?php echo usp_get_username( usp_office_id() ); ?></div>
        <div class="usp-info__meta"><?php do_action( 'usp_info_meta', $user_id ); ?></div>
    </div>

    <?php echo usp_get_quote_box( $user_id, [ 'side' => 'top' ] ); ?>

    <div class="usp-info__content"><?php do_action( 'usp_info_content', $user_id ); ?></div>

    <?php echo usp_show_user_custom_fields( $user_id ); ?>

    <div class="usp-info__stats usps usps__line-1"><?php do_action( 'usp_info_stats', $user_id ); ?></div>

    <div class="usp-info__bottom"><?php do_action( 'usp_info_bottom', $user_id ); ?></div>
</div>
