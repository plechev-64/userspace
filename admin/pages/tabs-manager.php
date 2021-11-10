<?php

require_once USP_PATH . 'admin/classes/class-usp-tabs-manager.php';

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$areaType = ( isset( $_GET['area-type'] ) ) ? sanitize_text_field( wp_unslash( $_GET['area-type'] ) ) : 'area-menu';

$tabsManager = new USP_Tabs_Manager( $areaType );

$title    = __( 'User profile page tabs manager', 'userspace' );
$subtitle = __( 'On this page you can create new tabs for user profile page with arbitrary content and manage existing tabs in different areas on user profile page', 'userspace' );

$header = usp_get_admin_header( $title, $subtitle );

$tabs = $tabsManager->form_navi();

$tabs .= $tabsManager->get_manager();

$content = usp_get_admin_content( $tabs );

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $header . $content;
