<?php

defined( 'ABSPATH' ) || exit;

// additional controls
require_once 'extends-customize-controls.php';

// realtime customize preview
add_action( 'customize_preview_init', 'usp_customizer_live_preview' );
function usp_customizer_live_preview() {
	wp_enqueue_script(
		'usp-customizer-js',
		plugins_url( 'assets/js/customizer-preview.js', __FILE__ ),
		[ 'jquery', 'customize-preview' ],
		'1.0.0',
		true
	);
}

// left panel customizer styles
add_action( 'customize_controls_print_footer_scripts', 'usp_customizer_general_style' );
function usp_customizer_general_style() {
	wp_enqueue_style(
		'usp-panel-customizer',
		plugins_url( 'assets/css/panel-customizer.css', __FILE__ ),
		'1.0.0',
		USP_VERSION,
	);
}

// left panel customizer scripts
add_action( 'customize_controls_enqueue_scripts', 'usp_customizer_general_script' );
function usp_customizer_general_script() {
	wp_enqueue_script(
		'usp-panel-customizer',
		plugins_url( 'assets/js/panel-customizer.js', __FILE__ ),
		[ 'jquery', 'customize-controls' ],
		'1.0.0',
		true
	);
}


// customizer hierarchy:
// panel->section-1->setting-1
// panel->section-1->setting-2 ...
// panel->section-2->setting-1 ...
add_action( 'customize_register', 'usp_add_customizer' );
function usp_add_customizer( $wp_customize ) {
	$panel   = 'userspace-panel';
	$section = 'userspace-general';

	// Let's add the plugin panel. Sections will be linked to this panel.
	$wp_customize->add_panel( $panel, [    // ID panel
		'priority' => 20,
		'title'    => __( 'Settings UserSpace', 'userspace' ),
	] );

	##  Section 1  ##
	$wp_customize->add_section( $section, [ // ID section
		'title'    => __( 'General settings', 'userspace' ),
		'priority' => 10,
		'panel'    => $panel,              // the section is linked to the panel
	] );

	// option #1 in the section
	$wp_customize->add_setting( 'usp_customizer[usp_background]', [    // The option ID and its name and key in wp_options in the array
		'type'              => 'option',                                    // stored in wp_options (for plugins)
		'default'           => '#0369a1',                                   // default value
		'transport'         => 'postMessage',                               // realtime update. Requires data in the script
		'sanitize_callback' => 'sanitize_hex_color'                         // sanitize
	] );

	// the type of the colorpicker option in the 1st option
	// palette https://material.io/design/color/the-color-system.html#tools-for-picking-colors
	$wp_customize->add_control( new USP_Customize_Color( $wp_customize, 'usp_customizer[usp_background]', [
		'section'     => $section,
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
	$wp_customize->add_setting( 'usp_customizer[usp_color]', [
		'type'              => 'option',
		'default'           => '#ffffff',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'sanitize_hex_color',
	] );
	$wp_customize->add_control( new USP_Customize_Color( $wp_customize, 'usp_customizer[usp_color]', [
		'section' => $section,
		'label'   => __( 'Primary button text color:', 'userspace' ),
	] ) );


	// option #3 in the section
	$wp_customize->add_setting( 'usp_customizer[usp_bttn_size]', [
		'type'              => 'option',
		'default'           => '16',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'absint',
	] );

	$wp_customize->add_control( new USP_Customize_Range( $wp_customize, 'usp_customizer[usp_bttn_size]', [
		'section'     => $section,
		'label'       => __( 'Font size standard:', 'userspace' ),
		'description' => __( 'set the font size of the buttons from 12px to 24px (default is 16px)', 'userspace' ),
		'min'         => 12,
		'max'         => 24,
		'step'        => 1,
	] ) );

	/*
	 * UserSpace bar
	 */

	// notice before settings
	$wp_customize->add_setting( 'usp-note' );
	$wp_customize->add_control( new USP_Customize_Note( $wp_customize, 'usp-note-1', [               // ID
		'settings'    => 'usp-note',
		'section'     => $section,
		'label'       => __( 'UserSpace Bar', 'userspace' ),
		'description' => __( 'UserSpace Bar – is the top panel UserSpace plugin through which the plugin and its add-ons can output their data and the administrator can make his menu, forming it on <a href="/wp-admin/nav-menus.php" target="_blank">page management menu of the website</a>', 'userspace' ),
	] ) );

	// show/hide userspace bar
	$wp_customize->add_setting( 'usp_customizer[usp_bar_show]', [
		'type'      => 'option',
		'default'   => true,
		'transport' => 'postMessage',
	] );
	$wp_customize->add_control( new USP_Customize_Switch( $wp_customize, 'usp_customizer[usp_bar_show]', [
		'settings' => 'usp_customizer[usp_bar_show]',
		'section'  => $section,
		'label'    => __( 'Show UserSpace Bar', 'userspace' ),
	] ) );

	// usp bar color
	$wp_customize->add_setting( 'usp_customizer[usp_bar_color]', [
		'type'      => 'option',
		'default'   => 'Black',
		'transport' => 'postMessage',
	] );

	$wp_customize->add_control( 'usp_customizer[usp_bar_color]', [
		'section' => $section,
		'type'    => 'radio',
		'label'   => __( 'Color', 'userspace' ),
		'choices' => [
			'black'   => __( 'Black', 'userspace' ),
			'white'   => __( 'White', 'userspace' ),
			'primary' => __( 'Primary colors of UserSpace', 'userspace' ),
		]
	] );

	$wp_customize->add_setting( 'usp_customizer[usp_bar_opacity]', [
		'type'              => 'option',
		'default'           => '0.7',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'usp_sanitize_decimal',
	] );

	$wp_customize->add_control( new USP_Customize_Range( $wp_customize, 'usp_customizer[usp_bar_opacity]', [
		'section'     => $section,
		'label'       => __( 'Opacity UserSpace Bar:', 'userspace' ),
		'description' => __( 'set opacity of the UserSpace Bar from 0.5 to 1 (default is 0.7)', 'userspace' ),
		'min'         => 0.5,
		'max'         => 1,
		'step'        => 0.05,
	] ) );

	// usp bar width
	$wp_customize->add_setting( 'usp_customizer[usp_bar_width]', [
		'type'              => 'option',
		'default'           => 0,
		'transport'         => 'postMessage',
		'sanitize_callback' => 'absint',
	] );

	$wp_customize->add_control( 'usp_customizer[usp_bar_width]', [
		'section'     => $section,
		'type'        => 'number',
		'label'       => __( 'Width content area', 'userspace' ),
		'description' => __( 'Width in pixels. Default or 0: fullwidth. Example: 1280 (max width content of your site)', 'userspace' ),
	] );

	/*
	 * UserSpace bar END
	 */

	// separator
	$wp_customize->add_setting( 'usp-separator' );
	$wp_customize->add_control( new USP_Customize_Separator( $wp_customize, 'usp_bar_after', [     // ID
		'settings' => 'usp-separator',
		'section'  => $section,
	] ) );

}

// Sanitize Number Range
/** @noinspection PhpUnused */
function usp_sanitize_decimal( $number ) {
	return filter_var( $number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
}
