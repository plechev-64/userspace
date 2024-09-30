<?php

namespace USP\Core;

/**
 * UserSpace template includer class.
 *
 * This class connects templates from the current WordPress theme or from a special plugin directory.
 * If not exist, it will connect the file from the plugin folder
 *
 * @since 1.0
 */
class Template {

	private string $name;
	private string $file;

	public function __construct( string $name, string $file = '' ) {
		$this->name = $name;
		$this->file = $file;
	}

	public function include( array|bool $vars = [] ): void {

		if ( ! empty( $vars ) && is_array( $vars ) ) {
			extract( $vars );
		}

		$path = $this->get_path();

		if ( ! $path ) {
			return;
		}

		do_action( 'usp_include_template_before', $this->name, $path );

		include $path;

		do_action( 'usp_include_template_after', $this->name, $path );
	}

	public function get_content( array|bool $vars = [] ): string {

		ob_start();

		$this->include( $vars );

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	public function get_path(): ?string {
		// find in the current WordPress theme (/wp-content/themes/your-active-WP-theme/userspace/templates/$temp_name)
		if ( file_exists( get_stylesheet_directory() . '/userspace/templates/' . $this->name ) ) {
			$path = get_stylesheet_directory() . '/userspace/templates';
		} // or from a special plugin directory (/wp-content/userspace/templates/$temp_name)
		else if ( file_exists( USP_TAKEPATH . 'templates/' . $this->name ) ) {
			$path = USP_TAKEPATH . 'templates';
		} // or connect the file from the plugin folder (/wp-content/plugins/your-plugin/templates/$temp_name)
		else {
			$path = ( $this->file ) ? plugin_dir_path( $this->file ) . 'templates' : USP_PATH . 'templates';
		}

		$path .= '/' . $this->name;

		$path = apply_filters( 'usp_template_path', $path, $this->name );

		if ( ! file_exists( $path ) ) {
			return null;
		}

		return $path;
	}

}
