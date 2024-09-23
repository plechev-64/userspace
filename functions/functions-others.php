<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

/**
 * Get URL to default cover.
 *
 * @param   $avatar_cover   bool    Set to 'true' for return avatar for cover (if the user did not set the cover).
 *                                  Default: false
 * @param   $user_id        int     ID of the user to get the avatar.
 *
 * @return  string          URL cover or avatar.
 *
 * @since   1.0.0
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
	if ( 'userspace/themes/default/index.php' === $current_id ) {
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

/**
 * Getting array of pages ID`s and post title.<br>
 * Return:
 * <pre>
 * Array (
 *      [0]     => Not selected
 *      [899]   => Chat
 *      ...
 * )
 * </pre>
 *
 * @return  array|false array: Page ID => post_title
 *
 * @since   1.0.0
 */
function usp_get_pages_ids() {
	$pages = ( new PostsQuery() )
		->select( [ 'ID', 'post_title' ] )
		->where( [ 'post_type' => 'page', 'post_status' => 'publish' ] )
		->limit( - 1 )
		->orderby( 'post_title', 'ASC' )
		->get_walker()->get_index_values( 'ID', 'post_title' );

	return [ __( 'Not selected', 'userspace' ) ] + $pages;
}

/**
 * Gets an array of the list of roles.<br>
 * Return:
 * <pre>
 * Array (
 *      [author] => Author
 *      [editor] => Editor
 * ...
 * )
 * </pre>
 *
 * @param   $exclude    array   Excluded roles (slug).
 *
 * @return  array       Roles slug (key) & roles name (value).
 *
 * @since   1.0.0
 */
function usp_get_roles_ids( $exclude = false ) {
	if ( ! is_admin() ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

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

/**
 * Converts Cyrillic to Latin and clears the string.
 * E.g. function is used to create slugs.
 *
 * @param   $string     string  Input string for cleaning.
 * @param   $sanitize   bool    Whitespace becomes a dash. False - returns spaces.
 *                              Default: true
 *
 * @return  string      Converted string.
 *
 * @since   1.0.0
 */
function usp_sanitize_string( $string, $sanitize = true ) {
	$string_to_lower = mb_strtolower( $string );

	$title = strtr( $string_to_lower,
		/**
		 * Filter allows you to replace or supplement the symbol table.
		 *
		 * @param array  Input => output value.
		 *
		 * @since       1.0.0
		 */
		apply_filters( 'usp_sanitize_iso', [
			"Ї" => "Yi",
			"ї" => "i",
			"Ґ" => "G",
			"ґ" => "g",
			"Ә" => "A",
			"Ғ" => "G",
			"Қ" => "K",
			"Ң" => "N",
			"Ө" => "O",
			"Ұ" => "U",
			"Ү" => "U",
			"H" => "H",
			"ә" => "a",
			"ғ" => "g",
			"қ" => "k",
			"ң" => "n",
			"ө" => "o",
			"ұ" => "u",
			"h" => "h",
			"Є" => "YE",
			"І" => "I",
			"Ѓ" => "G",
			"і" => "i",
			"№" => "N",
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
			"#" => "",
			"$" => "",
			"%" => "",
			"^" => "",
			"&" => "",
		] ) );

	return $sanitize ? sanitize_title_with_dashes( $title, '', 'save' ) : $title;
}

/**
 * Retrieves the list of emojis to the specified input field.
 *
 * @param   $id_area    string  ID of textarea to insert the emoji.
 * @param   $class      string  Additional class.
 *
 * @return  string   Emoji box.
 *
 * @since   1.0.0
 */
function usp_get_emoji( $id_area, $class = false ) {
	$emoji_box = '<div class="' . $class . ' usp-emoji usps usps__jc-end" data-area="' . $id_area . '">';
	$emoji_box .= '<i class="uspi fa-beaming-face-with-smiling-eyes" aria-hidden="true"></i>';
	$emoji_box .= '<div class="usp-emoji__list"><div class="usp-emoji__all usps usps__jc-between usps__radius-3"></div></div>';
	$emoji_box .= '</div>';

	return $emoji_box;
}

/**
 * Send HTML emails from UserSpace.
 *
 * @param   $email  string|array    Array or comma-separated list of email addresses to send message.
 * @param   $title          string  Email subject.
 * @param   $text           string  Message contents.
 * @param   $from           array   Optional. From 'name' and 'email'.
 *                                  Default: bloginfo name and noreply@ 'HTTP_HOST'.
 * @param   $attachments    string  Optional. Attachments.
 *                                  Default: ""
 *
 * @return  bool    Whether the email was sent successfully.
 *
 * @since   1.0.0
 */
function usp_mail( $email, $title, $text, $from = false, $attachments = false ) {
	$from_name = $from['name'] ?? get_bloginfo( 'name' );
	$from_mail = ( isset( $from['email'] ) ) ? $from['email'] : 'noreply@' . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' );

	add_filter( 'wp_mail_content_type', function () {
		return "text/html";
	} );

	$headers = 'From: ' . $from_name . ' <' . $from_mail . '>' . "\r\n";

	$content = usp_get_include_template( 'usp-mail.php', false, [
		'mail_title'   => $title,
		'mail_content' => $text,
	] );

	return wp_mail( $email, $title, $content, $headers, $attachments );
}

/**
 * Creating a form with custom fields.
 *
 * @param   $args       array   Extra arguments:
 * <pre>
 * $args['fields']      array   Custom fields. See: Fields
 * $args['submit']      string  Text of submit button.
 * $args['icon']        string  Icon of submit button.
 *                              Default: fa-check-circle
 * $args['onclick']     string  JS function.
 * $args['structure']
 * $args['class']       string  Additional class.
 * $args['action']      string  Action in form.
 * $args['method']      string  post|get
 *                              Default: post
 * $args['submit_args'] array
 * $args['nonce_name']  string
 * </pre>
 *
 * @return  string  HTML form.
 *
 * @see     Fields  Data of Custom fields.
 * @see     Form    Create form.
 *
 * @since   1.0.0
 */
function usp_get_form( $args ) {
	$Form = new Form( $args );
	return $Form->get_form();
}

/**
 * Add notice box by type.
 *
 * @param   $args   array   Extra arguments:
 * <pre>
 * $args['type']        string      Type of notice.
 *                                  Default: info.
 *                                  Allowed: info|success|warning|error|simple
 * $args['title']       string      Title text.
 *                                  Default: none
 * $args['text']        string      Message text.
 * $args['text_center'] bool        Text align.
 *                                  Default: true
 *                                  Allowed: true (center position)|false (left position)
 * $args['icon']        bool|string Set icon. false - don't show, string - icon class. Example: 'fa-info'.
 *                                  Default: true
 * $args['class']       string      Additional class.
 * $args['no_border']   bool        Hide border color.
 *                                  Default: false
 * $args['cookie']      string      Unique cookie id.
 * $args['cookie_time'] int         Lifetime cookie.
 *                                  Default: 30 days
 * </pre>
 *
 * @return  string   HTML notice.
 *
 * @see     Notice
 *
 * @since   1.0.0
 */
function usp_get_notice( $args ) {
	require_once USP_PATH . '/src/Notice.php';

	$Notice = new Notice( $args );

	return $Notice->get_notice();
}

/**
 * Create button.
 *
 * @param   $args   array   Extra arguments:
 * <pre>
 * $args['id']              string  ID button.
 * $args['class']     string|array  Additional class (classes).
 * $args['style']           string  Inline styles.
 * $args['type']            string  Type of button.
 *                                  Available: clear|simple|primary
 *                                  Default: primary
 * $args['size']            string  Button size (you can specify your own value, if the available ones do not fit).
 *                                  Available: small|standard|medium|large|big
 *                                  Default: standard
 * $args['icon']            string  Icon (if necessary). Example: fa-car
 *                                  Default: none
 * $args['icon_align']      string  Position icon.
 *                                  Available: left|right
 *                                  Default: left
 * $args['icon_mask']       bool    1 - is mask on icon.
 *                                  Default: false
 * $args['label']           string  Text on button.
 * $args['title']           string  Title attribute. If not specified, it is taken from label.
 * $args['counter']         int     The counter on the button.
 *                                  Default: none
 * $args['onclick']         string  JS onclick function.
 * $args['href']            string  URL to button.
 *                                  Default: javascript:void(0);
 * $args['data']            array   Data attr.<br>
 *                                  Example: $args['data'] = ['post' => $post];
 * $args['avatar']          string  The image of the avatar received by the get_avatar() function.<br>
 *                                  Example:$args['avatar'] = get_avatar(3, 26);
 * $args['avatar_circle']   bool    Round avatar.
 *                                  Default: false
 * $args['content']         string  Custom content in button.
 * $args['submit']          bool    Submit in usp_submit_form() js. Provided that onclick is not set.
 *                                  Default: false
 * $args['status']          string  State of the button.
 *                                  Available: loading|disabled|active
 *                                  Default: none
 * $args['attrs']           array   Additional attributes.
 * $args['fullwidth']       bool    Fullwidth button.
 *                                  Default: false
 * </pre>
 *
 * @return  string   HTML button.
 *
 * @see     Button
 *
 * @since   1.0.0
 */
function usp_get_button( array $args ) {
	$bttn = new Button( $args );

	return $bttn->get_button();
}

/**
 * Array of area options.
 *
 * @return  array   Options area.
 *
 * @since   1.0.0
 */
function usp_get_area_options() {
	return [
		'menu'     => get_site_option( 'usp_fields_area-menu' ),
		'counters' => get_site_option( 'usp_fields_area-counters' ),
		'actions'  => get_site_option( 'usp_fields_area-actions' ),
	];
}

/**
 * Writes logs by date.
 * And puts them in the directory: site/wp-content/userspace/logs/
 *
 * @param   $title  string  Event title.
 * @param   $data   array   Array of recorded data.
 * @param   $force  bool    if it is necessary to ignore the settings in the admin panel and write it down forcibly.
 *
 * @return  void
 *
 * @see     Log
 *
 * @since   1.0.0
 */
function usp_add_log( $title, $data = false, $force = false ) {
	if ( ! $force && ! usp_get_option( 'usp_logger' ) ) {
		return;
	}

	$USPLog = new Log();

	$USPLog->insert_title( $title );

	if ( $data ) {
		$USPLog->insert_log( $data );
	}
}

/**
 * Checks if it's Blocks Editor (Gutenberg).
 *
 * @return  bool    true - block editor.
 *                  false - is not a block editor.
 *
 * @since   1.0.0
 */
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

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

/**
 * Set root inline css colors & size.
 *
 * @return  string  :root variables.
 *
 * @since   1.0.0
 */
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

	$size = usp_get_option_customizer( 'usp_bttn_size', 16 );

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
 * Declination by profile gender.
 * Applicable for the Russian language.
 *
 * @param   $user_id    int ID of user.
 *                          Available: '-1' - e.g. wp-cron. Returns: $data[0]
 *
 * @param   $data       array   Declination data.
 *                              Example: ['опубликовал','опубликовала']
 *
 * @return  string  Declination result. For example: опубликовала
 *
 * @since   1.0.0
 */
function usp_declination_by_sex( $user_id, $data ) {
	// e.g. wp_cron
	if ( '-1' == $user_id ) {
		return $data[0];
	}

	$sex = get_user_meta( $user_id, 'usp_sex', true );

	$declination = $data[0];

	if ( $sex ) {
		/**
		 * The filter allows you to change the value for declination.
		 *
		 * @param   $sex    string  Default: 'Woman'.
		 *
		 * @since   1.0.0
		 */
		$var = apply_filters( 'usp_declination_var', __( 'Woman', 'userspace' ) );

		$declination = ( $sex === $var ) ? $data[1] : $data[0];
	}

	return $declination;
}

/**
 * Fast declination for Russian: "подписчик, подписчика, подписчиков".
 * Similar to `_n()` & `_nx()` and does not depend on the translation file.
 *
 * @param   $number     int     Passing a number from the counter.
 *
 * @param   $variants   array   Declination data.
 *                              Example:['подписчик', 'подписчика', 'подписчиков']
 *
 * @return  string      e.g. ($number = 5) 'подписчиков'
 *
 * @since   1.0.0
 */
function usp_decline( $number, $variants = [ '', '', '' ] ) {
	$x = ( $xx = abs( $number ) % 100 ) % 10;

	return $variants[ ( $xx > 10 and $xx < 15 or ! $x or $x > 4 and $x < 10 ) ? 2 : ( 1 == $x ? 0 : 1 ) ];
}


/**
 * Register beat callback
 *
 * @param string $beat_name - beat name
 * @param array $actions - array of allowed callbacks for $beat_name
 *
 * @return void
 */
function usp_init_beat( string $beat_name, array $actions ) {
	global $usp_beats;

	$usp_beats[ $beat_name ] = $actions;
}

/**
 * Check if callback action for $beat_name exist
 *
 * @param string $beat_name
 * @param string $action
 *
 * @return bool
 */
function usp_beat_action_exist( string $beat_name, string $action ) {
	global $usp_beats;

	$beat_actions = $usp_beats[ $beat_name ] ?? [];

	return in_array( $action, $beat_actions );
}

/**
 * Gets the id of the current profile page.
 *
 * @return  int     ID of the current profile page.
 *
 * @since   1.0.0
 */
function usp_office_id() {
	return USP()->office()->get_owner_id();
}

/**
 * Encodes the given string with base64.
 *
 * @param   $data   mixed   Variable (usually an array or object) to encode as base64.
 *
 * @return  string|false    The base64 encoded string, or false if it cannot be encoded.
 *
 * @since   1.0.0
 */
function usp_encode( $data ) {
	$json = wp_json_encode( $data );
	if ( false === $json ) {
		return false;
	}

	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	return base64_encode( $json );
}

/**
 * Decodes the given string with base64.
 *
 * @param   $string string  string to decode from base64.
 *
 * @return  mixed|false Decoded variable (usually an array or object), or false if it cannot be decoded.
 *
 * @since   1.0.0
 */
function usp_decode( $string ) {
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
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
 * @param   $time_action    string  MySQL datetime format.
 *
 * @return  string  Human-readable time difference.
 *
 * @since   1.0.0
 */
function usp_human_time_diff( $time_action ) {
	// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
	$unix_current_time = current_time( 'timestamp' );
	$unix_time_action  = strtotime( $time_action );

	return human_time_diff( $unix_time_action, $unix_current_time );
}

/**
 * Shows in a human-readable format such as "27 june" or "27 june 2021".
 *
 * @param   $date   string  MySQL datetime format.
 * @param   $year   bool    Optional. Output the year as well.
 *
 * @return  string  Human-readable date.
 * @throws  Exception
 *
 * @since   1.0.0
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
 * @param   $date   string  MySQL datetime format.
 * @param   $year   bool    If necessary, output the year as well.
 *
 * @return  string  Human-readable date.
 * @throws  Exception
 *
 * @since   1.0.0
 */
function usp_human_days( $date, $year = false ) {
	$current_date     = get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ), 'Y-m-d' );
	$yesterday        = gmdate( 'Y-m-d', strtotime( "-1 days", strtotime( $current_date ) ) );
	$before_yesterday = gmdate( 'Y-m-d', strtotime( "-2 days", strtotime( $current_date ) ) );

	$action_date = gmdate( 'Y-m-d', strtotime( $date ) );
	if ( $current_date == $action_date ) {
		return __( 'Today', 'userspace' );
	} elseif ( $yesterday == $action_date ) {
		return __( 'Yesterday', 'userspace' );
	} elseif ( $before_yesterday == $action_date ) {
		return __( 'Two days ago', 'userspace' );
	}

	return usp_human_date_format( $date, $year );
}

/**
 * Applies the callback to the elements of the given arrays.
 *
 * @param   $callback   callable    A callable to run for each element in each array.
 * @param   $data       array       An array to run through the callback function.
 *
 * @return  mixed   Returns an array containing the results of applying the callback to
 *                  the corresponding index of array (and arrays if more arrays are provided)
 *                  used as arguments for the callback.
 *
 * @since   1.0.0
 */
function usp_recursive_map( $callback, $data ) {
	if ( is_array( $data ) ) {
		foreach ( $data as $k => $v ) {
			$data[ $k ] = usp_recursive_map( $callback, $v );
		}
	} else {
		$data = is_scalar( $data ) ? $callback( $data ) : $data;
	}

	return $data;
}
