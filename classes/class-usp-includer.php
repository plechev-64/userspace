<?php

class USP_Includer {

	public $cache = 0;
	public $cache_time = 3600;
	public $place;
	public $files = [];
	public $minify_dir;
	public $is_minify;
	public $deregister_scripts = [];

	function __construct() {
		global $usp_styles;
		$this->place = ( ! isset( $usp_styles['header'] ) ) ? 'header' : 'footer';
	}

	function include_styles() {
		global $usp_styles;

		$this->is_minify = usp_get_option( 'usp_minify_css', 1 );

		$this->minify_dir = USP_UPLOAD_PATH . 'css';

		// header loading
		if ( 'header' == $this->place ) {
			if ( ! $usp_styles ) {
				$usp_styles = [];
			}
			$usp_styles = $this->regroup( $usp_styles );
		}

		$usp_styles = $this->dequeue( apply_filters( 'usp_pre_include_styles', $usp_styles ) );

		if ( ! isset( $usp_styles[ $this->place ] ) ) {
			return false;
		}

		$forceUnion = isset( $usp_styles['force-union'] ) ? $usp_styles['force-union'] : [];

		if ( $this->is_minify || $forceUnion ) {
			$this->init_dir();
		} else if ( is_dir( $this->minify_dir ) ) {
			usp_remove_dir( $this->minify_dir );
		}

		foreach ( $usp_styles[ $this->place ] as $key => $url ) {

			// minify off. Load directly
			if ( ! $this->is_minify && ! in_array( $key, $forceUnion ) ) {
				wp_enqueue_style( $key, $url, false, USP_VERSION );
				continue;
			}

			$this->files['css'][ $key ]['path'] = usp_path_by_url( $url );
			$this->files['css'][ $key ]['url']  = $url;
		}

		if ( ! isset( $this->files['css'] ) || ! $this->files['css'] ) {
			return false;
		}
		$ids = [];
		foreach ( $this->files['css'] as $id => $file ) {
			$ids[] = $id . ':' . filemtime( $file['path'] );
		}

		$filename = $this->place . '-' . md5( implode( ',', $ids ) ) . '.css';
		$filepath = USP_UPLOAD_PATH . 'css/' . $filename;

		if ( ! file_exists( wp_normalize_path( $filepath ) ) ) {
			$this->create_file( $filename, 'css' );
		}

		wp_enqueue_style( 'usp-' . $this->place, USP_UPLOAD_URL . 'css/' . $filename, false, USP_VERSION );
	}

	function include_scripts() {
		global $usp_scripts;

		$this->is_minify = usp_get_option( 'usp_minify_js' );

		$this->minify_dir = USP_UPLOAD_PATH . 'js';

		// header loading
		if ( 'header' == $this->place ) {
			if ( ! $usp_scripts ) {
				$usp_scripts = [];
			}
			$usp_scripts = $this->regroup( $usp_scripts );
		}

		$usp_scripts = $this->dequeue( apply_filters( 'usp_pre_include_scripts', $usp_scripts ) );

		if ( ! isset( $usp_scripts[ $this->place ] ) ) {
			return false;
		}

		$in_footer  = ( 'footer' == $this->place ) ? true : false;
		$forceUnion = isset( $usp_scripts['force-union'] ) ? $usp_scripts['force-union'] : [];

		if ( $this->is_minify || $forceUnion ) {
			$this->init_dir();
		} else if ( is_dir( $this->minify_dir ) ) {
			usp_remove_dir( $this->minify_dir );
		}

		foreach ( $usp_scripts[ $this->place ] as $key => $url ) {

			// minify off. Load directly
			if ( ! $this->is_minify && ! in_array( $key, $forceUnion ) ) {
				$parents = isset( $usp_scripts['parents'][ $key ] ) ? $parents = array_merge( $usp_scripts['parents'][ $key ], [ 'jquery' ] ) : [ 'jquery' ];
				wp_enqueue_script( $key, $url, $parents, USP_VERSION, $in_footer );
				continue;
			}

			$this->files['js'][ $key ]['path'] = usp_path_by_url( $url );
			$this->files['js'][ $key ]['url']  = $url;
		}

		if ( ! isset( $this->files['js'] ) || ! $this->files['js'] ) {
			return false;
		}

		$parents = [ 'jquery' ];

		foreach ( $this->files['js'] as $key => $file ) {
			$ids[] = $key . ':' . filemtime( $file['path'] );
			if ( $this->is_minify && isset( $usp_scripts['parents'][ $key ] ) ) {
				$parents = array_merge( $usp_scripts['parents'][ $key ], $parents );
			}
		}

		$filename = $this->place . '-' . md5( implode( ',', $ids ) ) . '.js';
		$filepath = USP_UPLOAD_PATH . 'js/' . $filename;

		if ( ! file_exists( $filepath ) ) {
			$this->create_file( $filename, 'js' );
		}

		wp_enqueue_script( 'usp-' . $this->place . '-scripts', USP_UPLOAD_URL . 'js/' . $filename, $parents, USP_VERSION, $in_footer );
	}

	function init_dir() {
		if ( ! is_dir( $this->minify_dir ) ) {
			mkdir( $this->minify_dir );
			chmod( $this->minify_dir, 0755 );
		}
	}

	function create_file( $filename, $type ) {

		$filepath = $this->minify_dir . '/' . $filename;

		$f = fopen( $filepath, 'w' );

		$string = '';
		foreach ( $this->files[ $type ] as $id => $file ) {

			$file_string = file_get_contents( $file['path'] );

			//            if ( $type == 'css' ) {
			//                $urls = array();
			//                preg_match_all( '/(?<=url\()[A-zА-я0-9\-\_\/\"\'\.\?\s]*(?=\))/iu', $file_string, $urls );
			//                // $addon = (usp_addon_path( $file['path'] )) ? true : false;
			////                if ( $urls[0] ) {
			////
			////                    foreach ( $urls[0] as $u ) {
			////                        $imgs[] = ($addon) ? usp_addon_url( trim( $u, '\',\"' ), $file['path'] ) : USP_URL . 'css/' . trim( $u, '\',\"' );
			////                        $us[]   = $u;
			////                    }
			////
			////                    $file_string = str_replace( $us, $imgs, $file_string );
			////                }
			//            }

			$string .= $file_string;
		}

		if ( $type == 'js' ) {
			// removing comments: //
			$string = preg_replace( '#//.*#', '', $string );
		}

		// removing comments: /* */
		$string = preg_replace( '#/\*(?:[^*]*(?:\*(?!/))*)*\*/#', '', $string );
		// removing spaces, hyphenation, and tabs
		$string = str_replace( [ "\r\n", "\r", "\n", "\t" ], " ", $string );
		$string = preg_replace( '/ {2,}/', ' ', $string );

		fwrite( $f, $string );
		fclose( $f );

		return $filepath;
	}

	function regroup( $array ) {
		$forceUnion = [];
		if ( isset( $array['force-union'] ) ) {
			$forceUnion = $array['force-union'];
			unset( $array['force-union'] );
		}

		$new_array = [];

		if ( isset( $array['dequeue'] ) ) {
			$new_array['dequeue'] = $array['dequeue'];
			unset( $array['dequeue'] );
		}

		$new_array[ $this->place ] = $array;
		$new_array['force-union']  = $forceUnion;

		if ( isset( $new_array[ $this->place ]['footer'] ) ) {
			$new_array['footer'] = $new_array[ $this->place ]['footer'];
			unset( $new_array[ $this->place ]['footer'] );
		}

		if ( isset( $new_array[ $this->place ]['parents'] ) ) {
			$new_array['parents'] = $new_array[ $this->place ]['parents'];
			unset( $new_array[ $this->place ]['parents'] );
		}

		$array = $new_array;

		return $array;
	}

	function dequeue( $included ) {

		if ( isset( $included['dequeue'] ) ) {

			foreach ( $included['dequeue'] as $key ) {

				if ( isset( $included['header'][ $key ] ) ) {
					unset( $included['header'][ $key ] );
				} else if ( isset( $included['footer'][ $key ] ) ) {
					unset( $included['footer'][ $key ] );
				}
			}
		}

		return $included;
	}

	function get_ajax_includes() {

		$content = '';

		$styles = $this->get_ajax_styles();

		if ( $styles ) {
			$content .= $styles;
		}

		$scripts = $this->get_ajax_scripts();

		if ( $scripts ) {
			$content .= $scripts;
		}

		return $content;
	}

	function get_ajax_scripts() {

		$wp_scripts = wp_scripts();

		$remove = [
			'jquery',
			'jquery-core'
		];

		$scriptsArray = [];

		foreach ( $wp_scripts->queue as $k => $script_id ) {

			if ( in_array( $script_id, $remove ) ) {
				continue;
			}

			if ( strpos( $script_id, 'admin' ) !== false ) {
				continue;
			}

			$scriptsArray[] = $script_id;
		}

		if ( ! $scriptsArray ) {
			return false;
		}

		ob_start();

		$wp_scripts->do_items( $scriptsArray );

		$scripts = ob_get_contents();

		ob_end_clean();

		return $scripts;
	}

	function get_ajax_styles() {

		$wp_scripts = wp_styles();

		$scriptsArray = [];
		foreach ( $wp_scripts->queue as $k => $script_id ) {

			if ( strpos( $script_id, 'admin' ) !== false ) {
				continue;
			}

			$scriptsArray[] = $script_id;
		}

		if ( ! $scriptsArray ) {
			return false;
		}

		ob_start();

		$wp_scripts->do_items( $scriptsArray );

		$scripts = ob_get_contents();

		ob_end_clean();

		return $scripts;
	}

	function get_ajax_src_list_includes() {

		$styles = $this->get_ajax_src_list_styles();

		$scripts = $this->get_ajax_src_list_scripts();

		return array_merge( $styles, $scripts );
	}

	function get_ajax_src_list_scripts() {

		$wp_scripts = wp_scripts();

		$remove = [
			'jquery'
		];

		$scriptsArray = [];

		foreach ( $wp_scripts->queue as $k => $script_id ) {

			if ( in_array( $script_id, $remove ) ) {
				continue;
			}

			if ( strpos( $script_id, 'admin' ) !== false ) {
				continue;
			}

			$obj = $wp_scripts->registered[ $script_id ];

			$scriptsArray[] = $obj->src;
		}

		return $scriptsArray;
	}

	function get_ajax_src_list_styles() {

		$wp_scripts = wp_styles();

		$scriptsArray = [];
		foreach ( $wp_scripts->queue as $k => $script_id ) {

			if ( strpos( $script_id, 'admin' ) !== false ) {
				continue;
			}

			$obj = $wp_scripts->registered[ $script_id ];

			$scriptsArray[] = $obj->src;
		}

		return $scriptsArray;
	}

}

/**
 * Enqueue a CSS stylesheet.
 *
 * Registers the style if source provided (does NOT overwrite) and enqueues.
 *
 * @param string $id (handle) Name of the stylesheet. Should be unique.
 * @param string $url Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
 *                                      Default empty.
 * @param string[] $parents Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
 * @param bool $in_footer Optional. Load in footer.
 *                                      If set to false, load in header.
 * @param bool $force_union Optional. Set to minification.
 *
 * @since 1.0.0
 *
 */
function usp_enqueue_style( $id, $url, $parents = false, $in_footer = false, $force_union = false ) {
	global $usp_styles;

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( is_admin() || doing_action( 'login_enqueue_scripts' ) || isset( $_REQUEST['rest_route'] ) ) {

		wp_enqueue_style( $id, $url, $parents, USP_VERSION );

		return;
	}

	$search = str_replace( '\\', '/', ABSPATH );
	$url    = str_replace( '\\', '/', $url );

	// if we determined that the absolute path is specified, we get the URL to the style.css file
	//    if ( stristr( $url, $search ) ) {
	//        $url = usp_addon_url( 'style.css', $url );
	//    }
	// if the style is output in the footer
	if ( $in_footer || isset( $usp_styles['header'] ) ) {
		// if no duplicate style is found in the header
		if ( ! isset( $usp_styles['header'][ $id ] ) ) {
			$usp_styles['footer'][ $id ] = $url;
		}
	} else {
		$usp_styles[ $id ] = $url;
	}

	//if ( $force_union )
	//$usp_styles['force-union'][] = $id;
}

/**
 * Enqueue a script.
 *
 * Registers the script if $src provided (does NOT overwrite), and enqueues it.
 *
 * @param string $id (handle) Name of the stylesheet. Should be unique.
 * @param string $url Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
 *                                      Default empty.
 * @param string[] $parents Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
 * @param bool $in_footer Optional. Load in footer.
 *                                      If set to false, load in header.
 * @param bool $force_union Optional. Set to minification.
 *
 * @since 1.0.0
 *
 */
function usp_enqueue_script( $id, $url, $parents = false, $in_footer = false, $force_union = false ) {
	global $usp_scripts;

	if ( is_admin() || doing_action( 'login_enqueue_scripts' ) || USP_Ajax()->is_rest_request() ) {

		if ( $parents && USP_Ajax()->is_rest_request() ) {
			$k = array_search( 'usp-core-scripts', $parents );
			if ( $k !== false ) {
				unset( $parents[ $k ] );
			}
		}

		wp_enqueue_script( $id, $url, $parents, USP_VERSION, $in_footer );

		return;
	}

	// if the script is output in the footer
	if ( $in_footer || isset( $usp_scripts['header'] ) ) {
		// if no duplicate script is found in the header
		if ( ! isset( $usp_scripts['header'][ $id ] ) ) {
			$usp_scripts['footer'][ $id ] = $url;
		}
	} else {
		$usp_scripts[ $id ] = $url;
	}

	if ( $parents ) {
		$usp_scripts['parents'][ $id ] = $parents;
	}

	//if ( $force_union )
	//$usp_scripts['force-union'][] = $id;
}

function usp_dequeue_style( $style ) {
	global $usp_styles;

	if ( ! isset( $usp_styles['dequeue'] ) ) {
		$usp_styles['dequeue'] = [];
	}

	if ( is_array( $style ) ) {
		$usp_styles['dequeue'] = array_merge( $usp_styles['dequeue'], $style );
	} else {
		$usp_styles['dequeue'][] = $style;
	}
}

function usp_dequeue_script( $script ) {
	global $usp_scripts;

	if ( ! isset( $usp_scripts['dequeue'] ) ) {
		$usp_scripts['dequeue'] = [];
	}

	if ( is_array( $script ) ) {
		$usp_scripts['dequeue'] = array_merge( $usp_scripts['dequeue'], $script );
	} else {
		$usp_scripts['dequeue'][] = $script;
	}
}

add_action( 'wp_enqueue_scripts', 'usp_include_scripts', 10 );
add_action( 'wp_footer', 'usp_include_scripts', 10 );
function usp_include_scripts() {

	do_action( 'usp_enqueue_scripts' );

	$USP_Include = new USP_Includer();
	$USP_Include->include_styles();
	$USP_Include->include_scripts();
}

add_action( 'wp_footer', 'usp_localize_modules_list_frontend', 10 );
function usp_localize_modules_list_frontend() {
	echo usp_localize_modules_list();
}

add_action( 'admin_footer', 'usp_localize_modules_list_admin', 10 );
function usp_localize_modules_list_admin() {
	$screen = get_current_screen();

	if ( is_admin() && preg_match( '/(userspace_page|manage-userspace)/', $screen->base ) ) {
		echo usp_localize_modules_list();
	}
}

function usp_localize_modules_list() {
	return '<script>USP.used_modules = ' . wp_json_encode( USP()->get_used_modules() ) . '</script>';
}

// we reset the arrays of registered scripts and styles when calling the tab via ajax
add_action( 'usp_init_ajax_tab', 'usp_reset_wp_dependencies', 10 );
function usp_reset_wp_dependencies() {
	global $wp_scripts, $wp_styles;

	$wp_scripts->queue = [];
	$wp_styles->queue  = [];
}

// we attach the code for connecting scripts and styles called inside the tab
add_filter( 'usp_ajax_tab_content', 'usp_add_registered_scripts', 10 );
function usp_add_registered_scripts( $content ) {

	$USP_Include = new USP_Includer();

	add_filter( 'script_loader_src', 'usp_ajax_edit_version_scripts' );

	$content = $USP_Include->get_ajax_includes() . $content;

	return $content;
}

// adding an array of pluggable scripts to the returned result of a tab call via ajax
// to connect them via the getScripts function
//add_filter('usp_ajax_tab_result','usp_add_src_list_includes');
function usp_add_src_list_includes( $result ) {
	$USP_Include        = new USP_Includer();
	$result['includes'] = $USP_Include->get_ajax_src_list_includes();

	return $result;
}

// we generate our own version of the plug-in scripts when the tab is called by ajax
function usp_ajax_edit_version_scripts( $src ) {
	$removes = [
		'wp-includes/js/jquery/jquery.min.js',
		'wp-includes/js/jquery/jquery-migrate.min.js',
		'wp-includes/js/jquery/ui/core.min.js',
		'wp-content/plugins/userspace/assets/js/usp-core.js',
	];

	foreach ( $removes as $remove ) {
		if ( strpos( $src, $remove ) ) {
			return false;
		}
	}

	$srcData = explode( '?', $src );

	if ( isset( $srcData[1] ) ) {

		$str = 'ver=' . md5( current_time( 'mysql' ) );

		$src = str_replace( $srcData[1], $str, $src );
	}

	return $src;
}
