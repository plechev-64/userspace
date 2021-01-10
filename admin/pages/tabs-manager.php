<?php

require_once USP_PATH . 'admin/classes/class-usp-tabs-manager.php';

$areaType = (isset( $_GET['area-type'] )) ? $_GET['area-type'] : 'area-menu';

$tabsManager = new USP_Tabs_Manager( $areaType );

$content = '<h2>' . __( 'User profile page tabs manager', 'userspace' ) . '</h2>';

$content .= '<p>' . __( 'On this page you can create new tabs for user profile page with arbitrary content and manage existing tabs in different areas on user profile page', 'userspace' ) . '</p>';

$content .= $tabsManager->form_navi();

$content .= $tabsManager->get_manager();

echo $content;
