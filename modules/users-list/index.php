<?php

require_once 'classes/class-usp-users-list.php';

/**/
function usp_users_rows_style() {
    usp_enqueue_style( 'usp-users-rows', USP_URL . 'modules/users-list/assets/css/usp-users-rows.css', false, USP_VERSION );
}

function usp_users_cards_style() {
    usp_enqueue_style( 'usp-users-cards', USP_URL . 'modules/users-list/assets/css/usp-users-cards.css', false, USP_VERSION );
}

function usp_users_masonry_style() {
    usp_enqueue_style( 'usp-users-masonry', USP_URL . 'modules/users-list/assets/css/usp-users-masonry.css', false, USP_VERSION );
}
