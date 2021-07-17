<?php

require_once USP_PATH . 'admin/classes/class-usp-tabs-manager.php';

$areaType = ( isset( $_GET['area-type'] ) ) ? $_GET['area-type'] : 'area-menu';

$tabsManager = new USP_Tabs_Manager( $areaType );

$title    = __( 'User profile page tabs manager', 'userspace' );
$subtitle = __( 'On this page you can create new tabs for user profile page with arbitrary content and manage existing tabs in different areas on user profile page', 'userspace' );

$header = usp_get_admin_header( $title, $subtitle );

$tabs = $tabsManager->form_navi();

$tabs .= $tabsManager->get_manager();

$content = usp_get_admin_content( $tabs );

echo $header . $content;
