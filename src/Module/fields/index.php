<?php

require_once 'classes/FieldAbstract.php';
require_once 'classes/Field.php';
require_once 'classes/Fields.php';
require_once 'classes/types/FieldAgree.php';
require_once 'classes/types/FieldCheckbox.php';
require_once 'classes/types/FieldColor.php';
require_once 'classes/types/FieldCustom.php';
require_once 'classes/types/FieldDate.php';
require_once 'classes/types/FieldDynamic.php';
require_once 'classes/types/FieldEditor.php';
require_once 'classes/types/FieldSelect.php';
require_once 'classes/types/FieldMultiSelect.php';
require_once 'classes/types/FieldRadio.php';
require_once 'classes/types/FieldRange.php';
require_once 'classes/types/FieldRunner.php';
require_once 'classes/types/FieldText.php';
require_once 'classes/types/FieldPhone.php';
require_once 'classes/types/FieldNumber.php';
require_once 'classes/types/FieldTextArea.php';
require_once 'classes/types/FieldUploader.php';
require_once 'classes/types/FieldFile.php';
require_once 'classes/types/FieldHidden.php';
require_once 'classes/types/FieldSwitch.php';

if ( usp_is_ajax() ) {
	usp_fields_scripts();
} else if ( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'usp_fields_scripts', 10 );
} else {
	add_action( 'usp_enqueue_scripts', 'usp_fields_scripts', 10 );
}

function usp_fields_scripts() {
	usp_enqueue_style( 'usp-fields', USP_URL . 'src/Module/fields/assets/css/usp-fields.css', false, false, true );
	usp_enqueue_script( 'usp-fields', USP_URL . 'src/Module/fields/assets/js/usp-fields.js', [ 'usp-core-scripts' ], false, true );
}
