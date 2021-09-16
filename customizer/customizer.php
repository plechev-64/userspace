<?php

defined( 'ABSPATH' ) || exit;

// additional controls
require_once 'extends-customize-controls.php';

// realtime customize preview
add_action( 'customize_preview_init', 'usp_customizer_live_preview' );
function usp_customizer_live_preview() {
	wp_enqueue_script(
		'usp-customizer-js',
		plugins_url( 'assets/js/customizer.js', __FILE__ ),
		[ 'jquery', 'customize-preview' ],
		'1.0.0',
		true
	);
}

// left panel customizer styles
add_action( 'customize_controls_print_footer_scripts', 'usp_customizer_general_style' );
function usp_customizer_general_style() {
	wp_enqueue_style(
		'usp-general-customizer-css',
		plugins_url( 'assets/css/customizer.css', __FILE__ ),
		'1.0.0'
	);
}


// customizer hierarchy:
// panel->section-1->setting-1
// panel->section-1->setting-2 ...
// panel->section-2->setting-1 ...
add_action( 'customize_register', 'usp_add_customizer' );
function usp_add_customizer( $wp_customize ) {

	// Let's add the plugin panel. Sections will be linked to this panel.
	$wp_customize->add_panel( 'user-space-panel', [    // ID panel
		'priority' => 20,
		'title'    => __( 'Settings UserSpace', 'userspace' ),
	] );

	##  Section 1  ##
	$wp_customize->add_section( 'user-space-general', [ // ID section
		'title'    => __( 'General settings', 'userspace' ),
		'priority' => 10,
		'panel'    => 'user-space-panel',              // the section is linked to the panel
	] );

	// option #1 in the section
	$wp_customize->add_setting( 'usp-customizer[usp_background]', [    // The option ID and its name and key in wp_options in the array
		'type'              => 'option',                                    // stored in wp_options (for plugins)
		'default'           => '#0369a1',                                   // default value
		'transport'         => 'postMessage',                               // realtime update. Requires data in the script
		'sanitize_callback' => 'sanitize_hex_color'                         // sanitize
	] );

	// the type of the colorpicker option in the 1st option
	// palette https://material.io/design/color/the-color-system.html#tools-for-picking-colors
	$wp_customize->add_control( new USP_Customize_Color( $wp_customize, 'usp-customizer[usp_background]', [
		'section'     => 'user-space-general',
		'label'       => __( 'Primary button background color:', 'userspace' ),
		'description' => __( 'Go to your personal account and configure the buttons:', 'userspace' ),
		'palette'     => [
			'#000000',
			'#ffffff',
			'#D32F2F',
			'#C2185B',
			'#7B1FA2',
			'#512DA8',
			'#303F9F',
			'#1976D2',
			'#0288D1',
			'#0097A7',
			'#00796B',
			'#388E3C',
			'#689F38',
			'#AFB42B',
			'#FBC02D',
			'#FFA000',
			'#F57C00',
			'#E64A19',
			'#5D4037',
			'#616161',
			'#455A64',
		],
	] ) );


	// option #2 in the section
	$wp_customize->add_setting( 'usp-customizer[usp_color]', [
		'type'              => 'option',
		'default'           => '#ffffff',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	] );
	$wp_customize->add_control( new USP_Customize_Color( $wp_customize, 'usp-customizer[usp_color]', [
		'section' => 'user-space-general',
		'label'   => __( 'Primary button text color:', 'userspace' ),
	] ) );


	// option #3 in the section
	$wp_customize->add_setting( 'usp-customizer[usp_bttn_size]', [
		'type'              => 'option',
		'default'           => '15',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'absint',
	] );

	$wp_customize->add_control( new USP_Customize_Range( $wp_customize, 'usp-customizer[usp_bttn_size]', [
		'section'     => 'user-space-general',
		'label'       => __( 'Font size standart:', 'userspace' ),
		'description' => __( 'set the font size of the buttons from 12px to 24px (default is 15px)', 'userspace' ),
		'min'         => 12,
		'max'         => 24,
		'step'        => 1,

	] ) );

	// separator
	$wp_customize->add_setting( 'usp-separator', [ 'default' => '', 'sanitize_callback' => 'esc_html' ] );
	$wp_customize->add_control( new USP_Customize_Separator( $wp_customize, 'usp-separator-1', [     // ID
		'settings' => 'usp-separator',
		'section'  => 'user-space-general',
	] ) );

	// заметка
	$wp_customize->add_setting( 'usp-note', [ 'default' => '', 'sanitize_callback' => 'esc_html' ] );
	$wp_customize->add_control( new USP_Customize_Note( $wp_customize, 'usp-note-1', [               // ID
		'settings'    => 'usp-note',
		'section'     => 'user-space-general',
		'label'       => 'Заголовок заметки:',
		'description' => 'Здесь будет содержимое <strong>заметки</strong>',
	] ) );

	##  END Section 1  ##

}
