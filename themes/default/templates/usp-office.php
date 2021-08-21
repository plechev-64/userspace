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

$office_owner = USP()->office()->owner();
$has_sidebar =  ( is_active_sidebar( 'usp_theme_sidebar' ) ) ? 'usp-profile__has-sidebar':'';
?>

<div id="usp-office-profile" class="usp-office-profile usps usps__column usps__nowrap usps__relative">
    <div class="usp-office-top usps usps__jc-end"><?php do_action( 'usp_area_top' ); ?></div>
    <div class="usp-office-card usps usps__nowrap usps__relative">
        <div class="usp-office-left usps usps__column">
			<?php echo usp_get_avatar( $office_owner->ID, 200, false, $args, $html ); ?>
            <div class="usp-under-ava"><?php do_action( 'usp_area_under_ava' ); ?></div>
        </div>

        <div class="usp-office-right usps usps__column usps__grow usps__jc-between">
            <div class="usp-office-usermeta usps usps__column">
                <div class="usp-office-title usps">
                    <div class="usp-user-name"><?php echo $office_owner->get_username(); ?></div>
                    <div class="usp-action"><?php echo $office_owner->get_action_icon(); ?></div>
                </div>
                <div class="usp-user-icons"><?php do_action( 'usp_area_icons' ); ?></div>
            </div>

            <div class="usp-office-middle usps usps__column usps__grow">
                <div class="usp-office-bttn-act"><?php echo USP()->tabs()->get_menu( 'actions' ); ?></div>
                <div class="usp-office-box"><?php do_action( 'usp_area_box' ); ?></div>
            </div>

            <div class="usp-office-bottom">
                <div class="usp-office-bttn-lite usps usps__jc-end"><?php echo USP()->tabs()->get_menu( 'counters' ); ?></div>
            </div>
        </div>
    </div>
</div>

<div id="usp-tabs" class="usp-tab-area usps usps__nowrap usps__relative">
	<?php echo USP()->tabs()->get_menu( 'menu', [
		'class' => usp_get_option( 'usp_office_tab_type', 1 ) ? FALSE : 'usps__column',
	] ); ?>

    <div class="usp-profile-content <?php echo $has_sidebar; ?> usps usps__nowrap usps__grow">
	    <?php if ( $current = USP()->tabs()->current() ) {
		    echo $current->get_content();
	    } ?>

		<?php if ( function_exists( 'dynamic_sidebar' ) && is_active_sidebar( 'usp_theme_sidebar' ) ) { ?>
            <div class="usp-profile__sidebar">
				<?php dynamic_sidebar( 'usp_theme_sidebar' ); ?>
            </div>
		<?php } ?>
    </div>
</div>
