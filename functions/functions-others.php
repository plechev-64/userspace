<?php

/**
 * Get url to default cover
 *
 * @param bool $avatar_cover set to 'true' for return avatar for cover (if the user did not set the cover).
 *                               Default: false
 * @param int $user_id id of the user to get the avatar.
 *
 * @return string url cover or avatar.
 * @since 1.0
 *
 */
function usp_get_default_cover( $avatar_cover = false, $user_id = false ) {
	$default_cover = USP_URL . 'themes/default/assets/img/usp-default-cover.jpg';

	if ( $avatar_cover && $user_id ) {
		$avatar = get_user_meta( $user_id, 'usp_avatar', 1 );
		if ( $avatar ) {
			$default_cover = get_avatar_url( $user_id, [ 'size' => 1000 ] );
		}
	}

	$current_id = usp_get_option( 'usp_current_office' );

	// default userspace theme user account
	if ( $current_id === 'userspace/themes/default/index.php' ) {
		return $default_cover;
	}

	// other theme
	if ( in_array( $current_id, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$file = dirname( plugins_url() . '/' . $current_id ) . '/assets/img/usp-default-cover.jpg';

		if ( file_exists( $file ) ) {
			return $file;
		}
	}

	return $default_cover;
}

// register menu in userspace bar
add_action( 'after_setup_theme', 'usp_register_userspace_menu' );
function usp_register_userspace_menu() {
	if ( ! usp_get_option_customizer( 'usp_bar_show', 1 ) ) {
		return;
	}

	register_nav_menu( 'usp-bar', __( 'UserSpace Bar', 'userspace' ) );
}

if ( ! function_exists( 'get_called_class' ) ) :
	function get_called_class() {
		$arr       = [];
		$arrTraces = debug_backtrace();
		foreach ( $arrTraces as $arrTrace ) {
			if ( ! array_key_exists( "class", $arrTrace ) ) {
				continue;
			}
			if ( count( $arr ) == 0 ) {
				$arr[] = $arrTrace['class'];
			} elseif ( get_parent_class( $arrTrace['class'] ) == end( $arr ) ) {
				$arr[] = $arrTrace['class'];
			}
		}

		return end( $arr );
	}

endif;
//getting array of pages IDs and titles
//for using in settings: ID => post_title
function usp_get_pages_ids() {
	$pages = ( new USP_Posts_Query() )
		->select( [ 'ID', 'post_title' ] )
		->where( [ 'post_type' => 'page', 'post_status' => 'publish' ] )
		->limit( - 1 )
		->orderby( 'post_title', 'ASC' )
		->get_walker()->get_index_values( 'ID', 'post_title' );

	return [ __( 'Not selected', 'userspace' ) ] + $pages;
}

/**
 * Gets an array of the list of roles
 *
 * @param array $exclude excluded roles (slug).
 *
 * @return array Roles slug (key) & roles name (value).
 * @since 1.0
 *
 */
function usp_get_roles_ids( $exclude = false ) {
	$editable_roles = array_reverse( get_editable_roles() );

	$roles = [];

	foreach ( $editable_roles as $role => $details ) {
		if ( $exclude && in_array( $role, $exclude ) ) {
			continue;
		}

		$roles[ $role ] = translate_user_role( $details['name'] );
	}

	return $roles;
}

function usp_sanitize_string( $name, $sanitize = true ) {
	$name_lower = mb_strtolower( $name );

	$title = strtr( $name_lower,
		apply_filters( 'usp_sanitize_iso', [
			"Є" => "YE",
			"І" => "I",
			"Ѓ" => "G",
			"і" => "i",
			"№" => "#",
			"є" => "ye",
			"ѓ" => "g",
			"А" => "A",
			"Б" => "B",
			"В" => "V",
			"Г" => "G",
			"Д" => "D",
			"Е" => "E",
			"Ё" => "YO",
			"Ж" => "ZH",
			"З" => "Z",
			"И" => "I",
			"Й" => "J",
			"К" => "K",
			"Л" => "L",
			"М" => "M",
			"Н" => "N",
			"О" => "O",
			"П" => "P",
			"Р" => "R",
			"С" => "S",
			"Т" => "T",
			"У" => "U",
			"Ф" => "F",
			"Х" => "H",
			"Ц" => "CZ",
			"Ч" => "CH",
			"Ш" => "SH",
			"Щ" => "SHH",
			"Ъ" => "",
			"Ы" => "Y",
			"Ь" => "",
			"Э" => "E",
			"Ю" => "YU",
			"Я" => "YA",
			"а" => "a",
			"б" => "b",
			"в" => "v",
			"г" => "g",
			"д" => "d",
			"е" => "e",
			"ё" => "yo",
			"ж" => "zh",
			"з" => "z",
			"и" => "i",
			"й" => "j",
			"к" => "k",
			"л" => "l",
			"м" => "m",
			"н" => "n",
			"о" => "o",
			"п" => "p",
			"р" => "r",
			"с" => "s",
			"т" => "t",
			"у" => "u",
			"ф" => "f",
			"х" => "h",
			"ц" => "cz",
			"ч" => "ch",
			"ш" => "sh",
			"щ" => "shh",
			"ъ" => "",
			"ы" => "y",
			"ь" => "",
			"э" => "e",
			"ю" => "yu",
			"я" => "ya",
			"—" => "-",
			"«" => "",
			"»" => "",
			"…" => "",
		] ) );

	return $sanitize ? sanitize_title_with_dashes( $title, '', 'save' ) : $title;
}

/**
 * Retrieves the list of emojis to the specified input field
 *
 * @param string $id_area id of textarea to insert the emoji
 * @param string $class additional class
 *
 * @return string   emoji box.
 * @since 1.0
 *
 */
function usp_get_emoji( $id_area, $class = false ) {
	$emoji_box = '<div class="' . $class . ' usp-emoji usps usps__jc-end usps__relative" data-area="' . $id_area . '">';
	$emoji_box .= '<i class="uspi fa-beaming-face-with-smiling-eyes" aria-hidden="true"></i>';
	$emoji_box .= '<div class="usp-emoji__list"><div class="usp-emoji__all usps usps__jc-between usps__radius-3"></div></div>';
	$emoji_box .= '</div>';

	return $emoji_box;
}

/**
 * Send HTML emails from UserSpace.
 *
 * @param string|array $email Array or comma-separated list of email addresses to send message.
 * @param string $title Email subject
 * @param string $text Message contents.
 * @param array $from Optional. From 'name' and 'email' (default: bloginfo name and noreply@ 'HTTP_HOST').
 * @param string $attachments Optional. Attachments. (default: "").
 *
 * @return bool
 * @since 1.0
 *
 */
function usp_mail( $email, $title, $text, $from = false, $attachments = false ) {
	$from_name = $from['name'] ?? get_bloginfo( 'name' );
	$from_mail = $from['email'] ?? 'noreply@' . $_SERVER['HTTP_HOST'];

	add_filter( 'wp_mail_content_type', function () {
		return "text/html";
	} );

	$headers = 'From: ' . $from_name . ' <' . $from_mail . '>' . "\r\n";

	$content = usp_get_include_template( 'usp-mail.php', false, [
		'mail_title'   => $title,
		'mail_content' => $text,
	] );

	$content .= '<p><small>-----------------------------------------------------<br/>'
	            . __( 'This letter was created automatically, no need to answer it.', 'userspace' ) . '<br/>'
	            . '"' . get_bloginfo( 'name' ) . '"</small></p>';

	return wp_mail( $email, $title, $content, $headers, $attachments );
}

function usp_get_form( $args ) {
	USP()->use_module( 'forms' );

	$Form = new USP_Form( $args );

	return $Form->get_form();
}

/**
 * Add notice box by type
 *
 * @param array $args Extra options.
 *                        $args['type']           string          type notice. Default: info. Allowed: info,success,warning,error,simple
 *                        $args['title']          string          title text
 *                        $args['text']           string          text message
 *                        $args['text_center']    bool            true - text-align: center; false - left. Default true
 *                        $args['icon']           bool,string     left position icon; false - don't show, string - icon class. Example: 'fa-info'. Default true
 *                        $args['class']          string          additional class
 *                        $args['no_border']      bool            hide border color. default false
 *                        $args['cookie']         string          unique cookie id
 *                        $args['cookie_time']    int             lifetime cookie. Default 30 days
 *
 * @return string   notice.
 * @since 1.0
 *
 */
function usp_get_notice( $args ) {
	require_once USP_PATH . '/classes/class-usp-notice.php';

	$Notice = new USP_Notice( $args );

	return $Notice->get_notice();
}

function usp_get_button( array $args ) {
	$bttn = new USP_Button( $args );

	return $bttn->get_button();
}

function usp_get_area_options() {
	return [
		'menu'     => get_site_option( 'usp_fields_area-menu' ),
		'counters' => get_site_option( 'usp_fields_area-counters' ),
		'actions'  => get_site_option( 'usp_fields_area-actions' ),
	];
}

/**
 * Writes logs by date
 * and puts them in the directory: site/wp-content/userspace/logs/
 *
 * @param string $title Event title.
 * @param array $data Array of recorded data.
 * @param bool $force if it is necessary to ignore the settings in the admin panel and write it down forcibly.
 *
 * @return void
 * @since 1.0.0
 *
 */
function usp_add_log( $title, $data = false, $force = false ) {
	if ( ! $force && ! usp_get_option( 'usp_logger' ) ) {
		return;
	}

	$USPLog = new USP_Log();

	$USPLog->insert_title( $title );

	if ( $data ) {
		$USPLog->insert_log( $data );
	}
}

function usp_is_gutenberg() {
	if ( ! is_admin() ) {
		return false;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	if ( get_current_screen()->base !== 'post' ) {
		return false;
	}

	if ( isset( $_GET['classic-editor'] ) ) {
		return false;
	}

	// Gutenberg plugin is installed and activated.
	$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

	// Block editor since 5.0.
	$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

	if ( ! $gutenberg && ! $block_editor ) {
		return false;
	}

	return true;
}

// set root inline css colors
function usp_get_root_colors() {
	$background = usp_get_option_customizer( 'usp_background', '#0369a1' );
	[ $r, $g, $b ] = sscanf( $background, "#%02x%02x%02x" );

	$color = usp_get_option_customizer( 'usp_color', '#ffffff' );

	// darker rgb
	$rd = round( $r * 0.45 );
	$gd = round( $g * 0.45 );
	$bd = round( $b * 0.45 );

	// is brighter rgb
	$rl = round( $r * 1.4 );
	$gl = round( $g * 1.4 );
	$bl = round( $b * 1.4 );

	// inverse rgb
	$rf = round( 0.75 * ( 255 - $r ) );
	$gf = round( 0.75 * ( 255 - $g ) );
	$bf = round( 0.75 * ( 255 - $b ) );

	$size = usp_get_option_customizer( 'usp_bttn_size', 15 );

	return ':root{
				--uspSize:' . $size . 'px;
				--uspRgb:' . $r . ',' . $g . ',' . $b . ';
				--uspHex:' . $background . ';
				--uspText:' . $color . ';
				--uspRgbDark:' . $rd . ',' . $gd . ',' . $bd . ';
				--uspRgbLight:' . $rl . ',' . $gl . ',' . $bl . ';
				--uspRgbFlip:' . $rf . ',' . $gf . ',' . $bf . ';
			}';
}

/**
 * Declination by profile gender
 * Applicable for Russian
 *
 * @param int $user_id id user.
 *
 * @param array $data = ['опубликовал','опубликовала']
 *
 * @return string declination result. For example: опубликовала
 * @since 1.0
 *
 */
function usp_declination_by_sex( $user_id, $data ) {
	// e.g. wp_cron
	if ( $user_id == '-1' ) {
		return $data[0];
	}

	$sex = get_user_meta( $user_id, 'usp_sex', true );

	$declination = $data[0];

	if ( $sex ) {
		$var = apply_filters( 'usp_declination_var', __( 'Woman', 'userspace' ) );

		$declination = ( $sex === $var ) ? $data[1] : $data[0];
	}

	return $declination;
}

/**
 * Fast declination for Russian: "подписчик, подписчика, подписчиков"
 * similar to _n() & _nx() and does not depend on the translation file
 *
 * @param int $number Passing a number from the counter.
 *
 * @param array $variants ['подписчик', 'подписчика', 'подписчиков']
 *
 * @return string   e.g. ($number = 5) 'подписчиков'
 * @since 1.0
 *
 */
function usp_decline( $number, $variants = [ '', '', '' ] ) {
	$x = ( $xx = abs( $number ) % 100 ) % 10;

	return $variants[ ( $xx > 10 and $xx < 15 or ! $x or $x > 4 and $x < 10 ) ? 2 : ( $x == 1 ? 0 : 1 ) ];
}

// userspace beat
function usp_init_beat( $beatName ) {
	global $usp_beats;

	$usp_beats[ $beatName ] = [];
}

/**
 * gets the id of the current profile page
 *
 * @return int  id of the current profile page
 * @since 1.0
 *
 */
function usp_office_id() {
	return USP()->office()->get_owner_id();
}

/**
 * Encodes the given string with base64.
 *
 * @param mixed $data Variable (usually an array or object) to encode as base64.
 *
 * @return string|false The base64 encoded string, or false if it cannot be encoded.
 * @since 1.0
 *
 */
function usp_encode( $data ) {
	$json = wp_json_encode( $data );
	if ( false === $json ) {
		return false;
	}

	return base64_encode( $json );
}

/**
 * Decodes the given string with base64.
 *
 * @param string $string string to decode from base64.
 *
 * @return mixed|false Decoded variable (usually an array or object), or false if it cannot be decoded.
 * @since 1.0
 *
 */
function usp_decode( $string ) {
	$decode = base64_decode( $string );
	if ( false === $decode ) {
		return false;
	}

	return json_decode( $decode );
}

/**
 * Determines the difference between the transmitted time and the current time.
 *
 * Is returned in a human-readable format such as "1 hour",
 * "5 mins", "2 days".
 *
 * @param string $time_action mysql datetime format.
 *
 * @return string Human-readable time difference.
 * @since 1.0.0
 *
 */
function usp_human_time_diff( $time_action ) {
	$unix_current_time = current_time( 'timestamp' );
	$unix_time_action  = strtotime( $time_action );

	return human_time_diff( $unix_time_action, $unix_current_time );
}

/**
 * Shows in a human-readable format such as "27 june" or "27 june 2021"
 *
 * @param string $date mysql datetime format
 * @param bool $year Optional. Output the year as well
 *
 * @return string Human-readable date
 * @since 1.0.0
 *
 */
function usp_human_date_format( $date, $year = false ) {
	global $wp_locale;
	$months = $wp_locale->month_genitive;

	$newDatetime = new Datetime( $date );
	$month       = $newDatetime->format( 'm' );

	$human = $newDatetime->format( 'j ' );
	$human .= $months[ $month ] . ' ';
	if ( $year ) {
		$human .= $newDatetime->format( 'Y' );
	}

	return $human;
}

/**
 * Returned in a human-readable format such as "Today", "Yesterday",
 * "Two days ago" or "27 june 2021".
 *
 * @param string $date mysql datetime format.
 * @param bool $year if necessary, output the year as well.
 *
 * @return string Human-readable date.
 * @since 1.0.0
 *
 */
function usp_human_days( $date, $year = false ) {
	$current_date     = get_date_from_gmt( date( 'Y-m-d H:i:s' ), 'Y-m-d' );
	$yesterday        = date( 'Y-m-d', strtotime( "-1 days", strtotime( $current_date ) ) );
	$before_yesterday = date( 'Y-m-d', strtotime( "-2 days", strtotime( $current_date ) ) );

	$action_date = date( 'Y-m-d', strtotime( $date ) );
	if ( $current_date == $action_date ) {
		return __( 'Today', 'userspace' );
	} elseif ( $yesterday == $action_date ) {
		return __( 'Yesterday', 'userspace' );
	} elseif ( $before_yesterday == $action_date ) {
		return __( 'Two days ago', 'userspace' );
	}

	return usp_human_date_format( $date, $year );
}
