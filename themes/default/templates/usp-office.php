<?php
/*
  Template v1.0
 */
$args = [
	'parent_wrap'  => 'div',
	'parent_id'    => 'usp-avatar',
	'class'        => 'usps__fit-cover',
	'parent_class' => 'usp-office-ava usps__relative',
];
$html = '<div class="usp-ava-bttns">' . apply_filters( 'usp_avatar_bttns', '' ) . '</div>';
?>

<div id="usp-office-profile" class="usp-office-profile usps usps__column usps__nowrap usps__relative">
    <div class="usp-office-top usps usps__jc-end"><?php do_action( 'usp_area_top' ); ?></div>
    <div class="usp-office-card usps usps__nowrap usps__relative">
        <div class="usp-office-left usps usps__column">
			<?php echo usp_get_avatar( usp_office_id(), 200, false, $args, $html ); ?>
            <div class="usp-under-ava"><?php do_action( 'usp_area_under_ava' ); ?></div>
        </div>

        <div class="usp-office-right usps usps__column usps__grow usps__jc-between">
            <div class="usp-office-usermeta usps usps__column">
                <div class="usp-office-title usps">
                    <div class="usp-user-name"><?php echo usp_get_username( usp_office_id() ); ?></div>
                    <div class="usp-action"><?php echo usp_get_useraction_html( usp_office_id(), $type = 2 ); ?></div>
                </div>
                <div class="usp-user-icons"><?php do_action( 'usp_area_icons' ); ?></div>
            </div>

            <div class="usp-office-middle usps usps__column usps__grow">
                <div class="usp-office-bttn-act"><?php do_action( 'usp_area_actions' ); ?></div>
                <div class="usp-office-box"><?php do_action( 'usp_area_box' ); ?></div>
            </div>

            <div class="usp-office-bottom">
                <div class="usp-office-bttn-lite usps usps__jc-end"><?php do_action( 'usp_area_counters' ); ?></div>
            </div>
        </div>
    </div>
</div>

<div id="usp-tabs" class="usp-tab-area usps usps__nowrap usps__relative">
	<?php do_action( 'usp_area_menu' ); ?>

    <div class="usp-profile-content usps usps__nowrap usps__grow">
		<?php do_action( 'usp_area_tabs' ); ?>

		<?php if ( function_exists( 'dynamic_sidebar' ) ) { ?>
            <div class="usp-profile__sidebar">
				<?php dynamic_sidebar( 'usp_theme_sidebar' ); ?>
            </div>
		<?php } ?>
    </div>
</div>
