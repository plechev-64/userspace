<?php

function usp_font_awesome_style() {
    wp_enqueue_style( 'usp-awesome', USP_URL . 'assets/usp-awesome/usp-awesome.min.css', false, USP_VERSION );
}

function usp_iconpicker() {
    wp_enqueue_style( 'usp-iconpicker', USP_URL . 'assets/usp-awesome/iconpicker/iconpicker.css', false, USP_VERSION );
    wp_enqueue_script( 'usp-iconpicker', USP_URL . 'assets/usp-awesome/iconpicker/iconpicker.js', array( 'jquery' ), USP_VERSION );
}

function usp_sortable_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-sortable' );
}

function usp_resizable_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-resizable' );
}

function usp_multiselect_scripts() {
    wp_enqueue_script( 'jquery' );
    usp_enqueue_style( 'f-select', USP_URL . 'assets/js/fselect/fSelect.css', false, USP_VERSION );
    wp_enqueue_script( 'f-select', USP_URL . 'assets/js/fselect/fSelect.js', false, USP_VERSION );
}

function usp_slider_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'jquery-touch-punch' );
}

function usp_datepicker_scripts() {
    wp_enqueue_style( 'jquery-ui-datepicker', USP_URL . 'assets/js/datepicker/style.css', false, USP_VERSION );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
}

function usp_image_slider_scripts() {
    usp_enqueue_style( 'jssor-slider', USP_URL . 'assets/css/slider.css' );
    wp_enqueue_script( 'jquery' );
    usp_enqueue_script( 'jssor-slider', USP_URL . 'assets/js/jssor.slider/js/jssor.slider.min.js' );
}

function usp_dialog_scripts() {
    usp_enqueue_style( 'ssi-modal', USP_URL . 'assets/js/ssi-modal/ssi-modal.min.css' );
    usp_enqueue_script( 'ssi-modal', USP_URL . 'assets/js/ssi-modal/ssi-modal.min.js' );
}

function usp_webcam_scripts() {
    usp_enqueue_script( 'say-cheese', USP_URL . 'assets/js/say-cheese/say-cheese.js', array(), true );
}

function usp_fileupload_scripts() {

    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-widget' );

    usp_enqueue_script( 'fileupload-load-image-all', USP_URL . 'assets/js/fileupload/js/load-image.all.min.js', array(), true );
    usp_enqueue_script( 'jquery-iframe-transport', USP_URL . 'assets/js/fileupload/js/jquery.iframe-transport.js', array(), true );
    usp_enqueue_script( 'jquery-fileupload', USP_URL . 'assets/js/fileupload/js/jquery.fileupload.js', array(), true );
    usp_enqueue_script( 'jquery-fileupload-process', USP_URL . 'assets/js/fileupload/js/jquery.fileupload-process.js', array(), true );
    usp_enqueue_script( 'jquery-fileupload-image', USP_URL . 'assets/js/fileupload/js/jquery.fileupload-image.js', array(), true );
}

function usp_crop_scripts() {
    wp_enqueue_style( 'jcrop' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jcrop' );
}

function usp_rangyinputs_scripts() {
    usp_enqueue_script( 'rangyinputs', USP_URL . 'assets/js/rangyinputs.js' );
}

function usp_animate_css() {
    usp_enqueue_style( 'animate-css', USP_URL . 'assets/css/animate-css/animate.min.css' );
}

add_action( 'login_enqueue_scripts', 'usp_enqueue_wp_form_scripts', 1 );
function usp_enqueue_wp_form_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/core.js', array( 'jquery' ), USP_VERSION );
    wp_enqueue_script( 'usp-primary-scripts', USP_URL . 'assets/js/scripts.js', array( 'jquery' ), USP_VERSION );

    usp_font_awesome_style();
    usp_fields_scripts();

    wp_localize_script( 'usp-core-scripts', 'USP', usp_get_localize_data() );
}

function usp_frontend_scripts() {
    global $user_ID, $user_LK, $post;

    usp_font_awesome_style();
    usp_animate_css();

    usp_enqueue_style( 'usp-core', USP_URL . 'assets/css/core.css' );
    usp_enqueue_style( 'usp-users-list', USP_URL . 'assets/css/users.css' );

    if ( ! is_user_logged_in() ) {
        usp_enqueue_style( 'usp-register-form', USP_URL . 'assets/css/regform.css' );
    }

    wp_enqueue_script( 'jquery' );

    if ( usp_is_office() ) {
        usp_dialog_scripts();
    }

    wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/core.js', array( 'jquery' ), USP_VERSION );
    usp_enqueue_script( 'usp-primary-scripts', USP_URL . 'assets/js/scripts.js' );

    $locData = usp_get_localize_data();

    if ( usp_get_option( 'difficulty_parole' ) ) {
        if ( ! $user_ID || usp_is_office( $user_ID ) ) {
            $locData['local']['pass0'] = __( 'Very weak', 'usp' );
            $locData['local']['pass1'] = __( 'Weak', 'usp' );
            $locData['local']['pass2'] = __( 'Worse than average', 'usp' );
            $locData['local']['pass3'] = __( 'Average', 'usp' );
            $locData['local']['pass4'] = __( 'Reliable', 'usp' );
            $locData['local']['pass5'] = __( 'Strong', 'usp' );
        }
    }

    $locData['post_ID']   = (isset( $post->ID ) && $post->ID) ? ( int ) $post->ID : ( int ) 0;
    $locData['office_ID'] = ($user_LK) ? ( int ) $user_LK : ( int ) 0;

    wp_localize_script( 'usp-core-scripts', 'USP', $locData );
}

function usp_get_localize_data() {
    global $user_ID;

    $local = array(
        'save'    => __( 'Save', 'usp' ),
        'close'   => __( 'Close', 'usp' ),
        'wait'    => __( 'Please wait', 'usp' ),
        'preview' => __( 'Preview', 'usp' ),
        'error'   => __( 'Error', 'usp' ),
        'loading' => __( 'Loading', 'usp' ),
        'upload'  => __( 'Upload', 'usp' ),
        'cancel'  => __( 'Cancel', 'usp' )
    );

    $data = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'wpurl'   => get_bloginfo( 'wpurl' ),
        'usp_url' => USP_URL,
        'user_ID' => ( int ) $user_ID,
        'nonce'   => wp_create_nonce( 'wp_rest' ),
        'local'   => apply_filters( 'usp_js_localize', $local ),
        'modules' => []
    );

    $data['mobile'] = (wp_is_mobile()) ? ( int ) 1 : ( int ) 0;
    $data['https']  = @( ! isset( $_SERVER["HTTPS"] ) || $_SERVER["HTTPS"] != 'on' ) ? ( int ) 0 : ( int ) 1;

    $data['errors']['required']      = __( 'Fill in all required fields', 'usp' );
    $data['errors']['pattern']       = __( 'Specify the data in the required format', 'usp' );
    $data['errors']['number_range']  = __( 'Specify a number within the allowed range', 'usp' );
    $data['errors']['file_max_size'] = __( 'File size is exceeded', 'usp' );
    $data['errors']['file_min_size'] = __( 'The insufficient size of the image', 'usp' );
    $data['errors']['file_max_num']  = __( 'Number of files exceeded', 'usp' );
    $data['errors']['file_accept']   = __( 'Invalid file type', 'usp' );

    return apply_filters( 'usp_init_js_variables', $data );
}

function usp_admin_scripts() {
    usp_enqueue_style( 'usp-core', USP_URL . 'assets/css/core.css' );
    wp_enqueue_style( 'animate-css', USP_URL . 'assets/css/animate-css/animate.min.css', false, USP_VERSION );
    wp_enqueue_style( 'usp-admin-style', USP_URL . 'admin/assets/style.css', false, USP_VERSION );
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/core.js', array( 'jquery' ), USP_VERSION );
    wp_enqueue_script( 'usp-admin-scripts', USP_URL . 'admin/assets/scripts.js', array( 'wp-color-picker' ), USP_VERSION );

    if ( ! usp_is_ajax() )
        wp_localize_script( 'usp-core-scripts', 'USP', usp_get_localize_data() );
}
