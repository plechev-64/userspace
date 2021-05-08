<?php

function usp_masonry_script() {
    usp_enqueue_script( 'usp-masonry', USP_URL . 'assets/lib/masonry/masonry.min.js' );
}

function usp_buttons_style() {
    usp_enqueue_style( 'usp-buttons', USP_URL . 'assets/css/usp-buttons.css', false, USP_VERSION );
}

function usp_font_awesome_style() {
    wp_enqueue_style( 'usp-awesome', USP_URL . 'assets/usp-awesome/usp-awesome.min.css', false, USP_VERSION );
}

function usp_iconpicker() {
    wp_enqueue_style( 'usp-iconpicker', USP_URL . 'assets/usp-awesome/iconpicker/iconpicker.min.css', false, USP_VERSION );
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
    usp_enqueue_style( 'f-select', USP_URL . 'assets/lib/fselect/fSelect.min.css', false, USP_VERSION );
    wp_enqueue_script( 'f-select', USP_URL . 'assets/lib/fselect/fSelect-min.js', false, USP_VERSION );
}

function usp_slider_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'jquery-touch-punch' );
}

function usp_datepicker_scripts() {
    wp_enqueue_style( 'jquery-ui-datepicker', USP_URL . 'assets/lib/datepicker/usp-datepicker.min.css', false, USP_VERSION );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );

    // localize datepicker - exclude En
    if ( get_locale() !== 'en_US' ) {
        wp_localize_jquery_ui_datepicker();
    }
}

function usp_image_slider_scripts() {
    usp_enqueue_style( 'jssor-slider', USP_URL . 'assets/lib/jssor-slider/slider.css' );

    wp_enqueue_script( 'jquery' );
    usp_enqueue_script( 'jssor-slider', USP_URL . 'assets/lib/jssor-slider/slider.min.js' );
}

function usp_dialog_scripts() {
    usp_enqueue_style( 'ssi-modal', USP_URL . 'assets/lib/ssi-modal/usp-ssi-modal.min.css' );
    usp_enqueue_script( 'ssi-modal', USP_URL . 'assets/lib/ssi-modal/ssi-modal.min.js' );
}

function usp_webcam_scripts() {
    usp_enqueue_script( 'say-cheese', USP_URL . 'assets/lib/say-cheese/say-cheese.js', array(), true );
}

function usp_fileupload_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-widget' );

    usp_enqueue_script( 'fileupload-load-image-all', USP_URL . 'assets/lib/fileupload/load-image.all.min.js', array(), true );
    usp_enqueue_script( 'jquery-iframe-transport', USP_URL . 'assets/lib/fileupload/jquery.iframe-transport.js', array(), true );
    usp_enqueue_script( 'jquery-fileupload', USP_URL . 'assets/lib/fileupload/jquery.fileupload.js', array(), true );
    usp_enqueue_script( 'jquery-fileupload-process', USP_URL . 'assets/lib/fileupload/jquery.fileupload-process.js', array(), true );
    usp_enqueue_script( 'jquery-fileupload-image', USP_URL . 'assets/lib/fileupload/jquery.fileupload-image.js', array(), true );
}

function usp_crop_scripts() {
    wp_enqueue_style( 'jcrop' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jcrop' );
}

function usp_rangyinputs_scripts() {
    usp_enqueue_script( 'rangyinputs', USP_URL . 'assets/lib/rangyinputs/rangyinputs.js' );
}

function usp_animate_css() {
    usp_enqueue_style( 'animate-css', USP_URL . 'assets/css/usp-animate.css' );
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
    global $user_ID;

    usp_font_awesome_style();
    usp_animate_css();
    usp_buttons_style();

    usp_enqueue_style( 'usp-core', USP_URL . 'assets/css/usp-core.css' );
    usp_enqueue_style( 'usp-users-list', USP_URL . 'assets/css/usp-users.css' );

    wp_enqueue_script( 'jquery' );

    if ( usp_is_office() ) {
        usp_dialog_scripts();
    }

    wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/core.js', array( 'jquery' ), USP_VERSION );
    usp_enqueue_script( 'usp-primary-scripts', USP_URL . 'assets/js/scripts.js' );

    $locData = usp_get_localize_data();

    if ( usp_get_option( 'difficulty_parole' ) ) {
        if ( ! $user_ID || usp_is_office( $user_ID ) ) {
            $locData['local']['pass0'] = __( 'Very weak', 'userspace' );
            $locData['local']['pass1'] = __( 'Weak', 'userspace' );
            $locData['local']['pass2'] = __( 'Worse than average', 'userspace' );
            $locData['local']['pass3'] = __( 'Average', 'userspace' );
            $locData['local']['pass4'] = __( 'Reliable', 'userspace' );
            $locData['local']['pass5'] = __( 'Strong', 'userspace' );
        }
    }

    wp_localize_script( 'usp-core-scripts', 'USP', $locData );
}

function usp_get_localize_data() {
    global $user_ID, $post, $user_LK;

    $local = array(
        'close'   => __( 'Close', 'userspace' ),
        'error'   => __( 'Error', 'userspace' ),
        'loading' => __( 'Loading', 'userspace' ),
        'upload'  => __( 'Upload', 'userspace' ),
        'cancel'  => __( 'Cancel', 'userspace' ),
        'search'  => __( 'Search', 'userspace' ),
    );

    $data = array(
        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
        'wpurl'     => get_bloginfo( 'wpurl' ),
        //'usp_url'   => USP_URL,
        'user_ID'   => ( int ) $user_ID,
        'office_ID' => ($user_LK) ? ( int ) $user_LK : ( int ) 0,
        'post_ID'   => (isset( $post->ID ) && $post->ID) ? ( int ) $post->ID : ( int ) 0,
        'nonce'     => wp_create_nonce( 'wp_rest' ),
        'local'     => apply_filters( 'usp_js_localize', $local ),
        'modules'   => []
    );

    //$data['mobile'] = (wp_is_mobile()) ? ( int ) 1 : ( int ) 0;
    $data['https'] = ( ! is_ssl() ) ? ( int ) 0 : ( int ) 1;

    $data['errors']['required']      = __( 'Fill in all required fields', 'userspace' );
    $data['errors']['pattern']       = __( 'Specify the data in the required format', 'userspace' );
    $data['errors']['number_range']  = __( 'Specify a number within the allowed range', 'userspace' );
    $data['errors']['file_max_size'] = __( 'File size is exceeded', 'userspace' );
    $data['errors']['file_min_size'] = __( 'The insufficient size of the image', 'userspace' );
    $data['errors']['file_max_num']  = __( 'Number of files exceeded', 'userspace' );
    $data['errors']['file_accept']   = __( 'Invalid file type', 'userspace' );

    return apply_filters( 'usp_init_js_variables', $data );
}

function usp_admin_scripts() {
    usp_buttons_style();
    usp_enqueue_style( 'usp-core', USP_URL . 'assets/css/usp-core.css' );
    wp_enqueue_style( 'animate-css', USP_URL . 'assets/css/usp-animate.css', false, USP_VERSION );
    wp_enqueue_style( 'usp-admin-style', USP_URL . 'admin/assets/usp-admin.css', false, USP_VERSION );
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'usp-core-scripts', USP_URL . 'assets/js/core.js', array( 'jquery' ), USP_VERSION );
    wp_enqueue_script( 'usp-admin-scripts', USP_URL . 'admin/assets/usp-admin.js', array( 'wp-color-picker' ), USP_VERSION );

    if ( ! usp_is_ajax() )
        wp_localize_script( 'usp-core-scripts', 'USP', usp_get_localize_data() );
}
