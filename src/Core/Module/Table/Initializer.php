<?php

namespace USP\Core\Module\Table;

class Initializer {

	public function init(): void {
		if ( usp_is_ajax() ) {
			$this->usp_table_scripts();
		} else if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'usp_table_scripts'], 10 );
		} else {
			add_action( 'usp_enqueue_scripts', [$this, 'usp_table_scripts'], 10 );
		}
	}

	public  function usp_table_scripts() {
		wp_enqueue_style( 'usp-table', USP_URL . 'assets/modules/table/usp-table.css' );
		wp_enqueue_script( 'usp-table', USP_URL . 'assets/modules/table/usp-table.js' );
	}

}