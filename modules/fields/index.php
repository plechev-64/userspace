<?php

require_once 'classes/class-usp-field-abstract.php';
require_once 'classes/class-usp-field.php';
require_once 'classes/class-usp-fields.php';
require_once 'classes/types/class-usp-field-agree.php';
require_once 'classes/types/class-usp-field-checkbox.php';
require_once 'classes/types/class-usp-field-color.php';
require_once 'classes/types/class-usp-field-custom.php';
require_once 'classes/types/class-usp-field-date.php';
require_once 'classes/types/class-usp-field-dynamic.php';
require_once 'classes/types/class-usp-field-editor.php';
require_once 'classes/types/class-usp-field-select.php';
require_once 'classes/types/class-usp-field-multiselect.php';
require_once 'classes/types/class-usp-field-radio.php';
require_once 'classes/types/class-usp-field-range.php';
require_once 'classes/types/class-usp-field-runner.php';
require_once 'classes/types/class-usp-field-text.php';
require_once 'classes/types/class-usp-field-tel.php';
require_once 'classes/types/class-usp-field-number.php';
require_once 'classes/types/class-usp-field-textarea.php';
require_once 'classes/types/class-usp-field-uploader.php';
require_once 'classes/types/class-usp-field-file.php';
require_once 'classes/types/class-usp-field-hidden.php';
function usp_fields_scripts() {
	usp_enqueue_style( 'usp-fields', USP_URL . 'modules/fields/assets/style.css', false, false, true );
	usp_enqueue_script( 'usp-fields', USP_URL . 'modules/fields/assets/scripts.js', ['usp-core-scripts' ], false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	usp_fields_scripts();
} else {
	add_action( 'usp_enqueue_scripts', 'usp_fields_scripts', 10 );
}
