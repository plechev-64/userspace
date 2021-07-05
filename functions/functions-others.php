<?php

/**
 * Get url to default cover
 *
 * @since 1.0
 *
 * @param bool  $avatar_cover   set to 'true' for return avatar for cover (if the user did not set the cover).
 *                              Default: false
 * @param int   $user_id        id of the user to get the avatar.
 *
 * @return string url cover or avatar.
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
    if ( $current_id === 'userspace/themes/default/index.php' )
        return $default_cover;

    // other theme
    if ( in_array( $current_id, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        $file = dirname( plugins_url() . '/' . $current_id ) . '/assets/img/usp-default-cover.jpg';

        if ( file_exists( $file ) )
            return $file;
    }

    return $default_cover;
}

/**
 * Get the description block with the desired side of the quote
 *
 * @since 1.0
 *
 * @param int $user_id      id user.
 * @param array $attr       $attr['side'] left|top (default: left)
 *                          $attr['text'] text of quote
 *                          $attr['class'] additional css class
 *
 * @return string description block.
 */
function usp_get_quote_box( $user_id, $attr = false ) {
    if ( ! isset( $attr['text'] ) ) {
        $user_description = get_the_author_meta( 'description', $user_id );
        if ( ! $user_description )
            return false;

        $descr = nl2br( wp_strip_all_tags( $user_description ) );
    } else {
        $descr = $attr['text'];
    }

    $side = isset( $attr['side'] ) ? 'usp-descr-' . $attr['side'] : 'usp-descr-left';

    $class = isset( $attr['class'] ) ? $attr['class'] . ' ' : '';

    return '<div class="' . $class . 'usp-descr-wrap usps ' . $side . '">'
        . '<div class="usp-descr usps__relative usps__radius-3">' . $descr . '</div>'
        . '</div>';
}

// register menu in userspace bar
add_action( 'after_setup_theme', 'usp_register_userspace_menu' );
function usp_register_userspace_menu() {
    if ( ! usp_get_option( 'usp_bar_show' ) )
        return;

    register_nav_menu( 'usp-bar', __( 'UserSpace Bar', 'userspace' ) );
}

if ( ! function_exists( 'get_called_class' ) ) :
    function get_called_class() {
        $arr       = array();
        $arrTraces = debug_backtrace();
        foreach ( $arrTraces as $arrTrace ) {
            if ( ! array_key_exists( "class", $arrTrace ) )
                continue;
            if ( count( $arr ) == 0 )
                $arr[] = $arrTrace['class'];
            else if ( get_parent_class( $arrTrace['class'] ) == end( $arr ) )
                $arr[] = $arrTrace['class'];
        }
        return end( $arr );
    }

endif;
//getting array of pages IDs and titles
//for using in settings: ID => post_title
function usp_get_pages_ids() {

    $pages = RQ::tbl( new USP_Posts_Query() )->select( [ 'ID', 'post_title' ] )
            ->where( [ 'post_type' => 'page', 'post_status' => 'publish' ] )
            ->limit( -1 )
            ->orderby( 'post_title', 'ASC' )
            ->get_walker()->get_index_values( 'ID', 'post_title' );

    $pages = array( __( 'Not selected', 'userspace' ) ) + $pages;

    return $pages;
}

/**
 * Gets an array of the list of roles
 *
 * @since 1.0
 *
 * @param array $exclude    excluded roles (slug).
 *
 * @return array Roles slug (key) & roles name (value).
 */
function usp_get_roles_ids( $exclude = false ) {
    $editable_roles = array_reverse( get_editable_roles() );

    $roles = [];

    foreach ( $editable_roles as $role => $details ) {
        if ( $exclude && in_array( $role, $exclude ) ) {
            continue;
        }

        $roles[$role] = translate_user_role( $details['name'] );
    }

    return $roles;
}

function usp_sanitize_string( $name, $sanitize = true ) {

    $name_lower = mb_strtolower( $name );

    $title = strtr( $name_lower, apply_filters( 'usp_sanitize_iso', [
        "Є" => "YE", "І" => "I", "Ѓ" => "G", "і" => "i", "№" => "#", "є" => "ye", "ѓ" => "g",
        "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
        "Е" => "E", "Ё" => "YO", "Ж" => "ZH",
        "З" => "Z", "И" => "I", "Й" => "J", "К" => "K", "Л" => "L",
        "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R",
        "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "H",
        "Ц" => "CZ", "Ч" => "CH", "Ш" => "SH", "Щ" => "SHH", "Ъ" => "",
        "Ы" => "Y", "Ь" => "", "Э" => "E", "Ю" => "YU", "Я" => "YA",
        "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
        "е" => "e", "ё" => "yo", "ж" => "zh",
        "з" => "z", "и" => "i", "й" => "j", "к" => "k", "л" => "l",
        "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
        "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
        "ц" => "cz", "ч" => "ch", "ш" => "sh", "щ" => "shh", "ъ" => "",
        "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
        "—" => "-", "«" => "", "»" => "", "…" => ""
        ] ) );

    return $sanitize ? sanitize_title_with_dashes( $title, '', 'save' ) : $title;
}

/**
 * Retrieves the list of emojis to the specified input field
 *
 * @since 1.0
 *
 * @param string    $id_area    id of textarea to insert the emoji
 *
 * @return string   emoji box.
 */
function usp_get_emoji( $id_area ) {
    $emoji_box = '<div class="usp-emoji usps usps__jc-end usps__relative" data-area="' . $id_area . '">';
    $emoji_box .= '<i class="uspi fa-beaming-face-with-smiling-eyes" aria-hidden="true"></i>';
    $emoji_box .= '<div class="usp-emoji__list"><div class="usp-emoji__all usps usps__jc-between usps__radius-3"></div></div>';
    $emoji_box .= '</div>';

    return $emoji_box;
}

/**
 * Send HTML emails from UserSpace.
 *
 * @since 1.0
 *
 * @param string|array  $email          Array or comma-separated list of email addresses to send message.
 * @param string        $title          Email subject
 * @param string        $text           Message contents.
 * @param array         $from           Optional. From 'name' and 'email' (default: bloginfo name and noreply@ 'HTTP_HOST').
 * @param string        $attachments    Optional. Attachments. (default: "").
 * @return bool
 */
function usp_mail( $email, $title, $text, $from = false, $attachments = false ) {

    $from_name = (isset( $from['name'] )) ? $from['name'] : get_bloginfo( 'name' );
    $from_mail = (isset( $from['email'] )) ? $from['email'] : 'noreply@' . $_SERVER['HTTP_HOST'];

    add_filter( 'wp_mail_content_type', function () {
        return "text/html";
    } );

    $headers = 'From: ' . $from_name . ' <' . $from_mail . '>' . "\r\n";

    $content = usp_get_include_template( 'usp-mail.php', false, [
        'mail_title'   => $title,
        'mail_content' => $text
        ] );

    $content .= '<p><small>-----------------------------------------------------<br/>
	' . __( 'This letter was created automatically, no need to answer it.', 'userspace' ) . '<br/>
	"' . get_bloginfo( 'name' ) . '"</small></p>';

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
 * @since 1.0
 *
 * @param array $args                   Extra options.
 *              $args['type']           string          type notice. Default: info. Allowed: info,success,warning,error,simple
 *              $args['title']          string          title text
 *              $args['text']           string          text message
 *              $args['text_center']    bool            true - text-align: center; false - left. Default true
 *              $args['icon']           bool,string     left position icon; false - don't show, string - icon class. Example: 'fa-info'. Default true
 *              $args['class']          string          additional class
 *              $args['no_border']      bool            hide border color. default false
 *              $args['cookie']         string          unique cookie id
 *              $args['cookie_time']    int             lifetime cookie. Default 30 days
 *
 * @return string   notice.
 */
function usp_get_notice( $args ) {
    require_once USP_PATH . '/classes/class-usp-notice.php';

    $Notice = new USP_Notice( $args );

    return $Notice->get_notice();
}

function usp_get_button( $args, $depr_url = false, $depr_args = false ) {

    if ( is_array( $args ) ) {
        $bttn = new USP_Button( $args );
        return $bttn->get_button();
    }

    _deprecated_argument( __FUNCTION__, '0.1.0' );

    $button = '<a href="' . $depr_url . '" ';
    if ( isset( $depr_args['attr'] ) && $depr_args['attr'] )
        $button .= $depr_args['attr'] . ' ';
    if ( isset( $depr_args['id'] ) && $depr_args['id'] )
        $button .= 'id="' . $depr_args['id'] . '" ';
    $button .= 'class="deprecated ';
    if ( isset( $depr_args['class'] ) && $depr_args['class'] )
        $button .= $depr_args['class'];
    $button .= '">';
    if ( isset( $depr_args['icon'] ) && $depr_args['icon'] )
        $button .= '<i class="uspi ' . $depr_args['icon'] . '"></i>';
    $button .= '<span>' . $args . '</span>';
    $button .= '</a>';
    return $button;
}

function usp_get_area_options() {

    $areas = array(
        'menu'     => get_site_option( 'usp_fields_area-menu' ),
        'counters' => get_site_option( 'usp_fields_area-counters' ),
        'actions'  => get_site_option( 'usp_fields_area-actions' ),
    );

    return $areas;
}

function usp_add_log( $title, $data = false, $force = false ) {

    if ( ! $force && ! usp_get_option( 'usp_logger' ) )
        return false;

    $USPLog = new USP_Log();

    $USPLog->insert_title( $title );

    if ( $data )
        $USPLog->insert_log( $data );
}

function usp_is_gutenberg() {
    global $post;

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
function usp_get_root_colors( $r, $g, $b, $usp_color ) {
    // darker rgb
    $rd = round( $r * 0.45 );
    $gd = round( $g * 0.45 );
    $bd = round( $b * 0.45 );

    // is brighter rgb
    $rl = round( $r * 1.4 );
    $gl = round( $g * 1.4 );
    $bl = round( $b * 1.4 );

    // inverse rgb
    $rf = round( 0.75 * (255 - $r) );
    $gf = round( 0.75 * (255 - $g) );
    $bf = round( 0.75 * (255 - $b) );

    // https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color
    $text_color = '#fff';
    $threshold  = apply_filters( 'usp_text_color_threshold', 150 );
    if ( ($r * 0.299 + $g * 0.587 + $b * 0.114) > $threshold ) {
        $text_color = '#000';
    }

    return ':root{
--uspText:' . $text_color . ';
--uspHex:' . $usp_color . ';
--uspRgb:' . $r . ',' . $g . ',' . $b . ';
--uspRgbDark:' . $rd . ',' . $gd . ',' . $bd . ';
--uspRgbLight:' . $rl . ',' . $gl . ',' . $bl . ';
--uspRgbFlip:' . $rf . ',' . $gf . ',' . $bf . ';
}';
}

/**
 * Declination by profile gender
 * Applicable for Russian
 *
 * @since 1.0
 *
 * @param int   $user_id    id user.
 *
 * @param array $data = ['опубликовал','опубликовала']
 *
 * @return string declination result. For example: опубликовала
 */
function usp_declination_by_sex( $user_id, $data ) {
    // e.g. wp_cron
    if ( $user_id == '-1' )
        return $data[0];

    $sex = get_user_meta( $user_id, 'usp_sex', true );

    $declination = $data[0];

    if ( $sex ) {
        $var = apply_filters( 'usp_declination_var', __( 'Woman', 'userspace' ) );

        $declination = ($sex === $var) ? $data[1] : $data[0];
    }

    return $declination;
}

/**
 * Fast declination for Russian: "подписчик, подписчика, подписчиков"
 * similar to _n() & _nx() and does not depend on the translation file
 *
 * @since 1.0
 *
 * @param int $number    Passing a number from the counter.
 *
 * @param array $variants  [ 'подписчик', 'подписчика', 'подписчиков' ]
 *
 * @return string   e.g. ($number = 5) 'подписчиков'
 */
function usp_decline( $number, $variants = [ '', '', '' ] ) {
    $x  = ($xx = abs( $number ) % 100) % 10;

    return $variants[($xx > 10 AND $xx < 15 OR ! $x OR $x > 4 AND $x < 10) ? 2 : ($x == 1 ? 0 : 1)];
}

// userspace beat
function usp_init_beat( $beatName ) {
    global $usp_beats;

    $usp_beats[$beatName] = [];
}

/**
 * gets the id of the current profile page
 *
 * @since 1.0
 *
 * @return int  id of the current profile page
 */
function usp_office_id() {
    global $user_LK;

    return ( int ) $user_LK;
}

/**
 * Encodes the given string with base64.
 *
 * @since 1.0
 *
 * @param mixed $data    Variable (usually an array or object) to encode as base64.
 *
 * @return string|false The base64 encoded string, or false if it cannot be encoded.
 */
function usp_encode( $data ) {
    $json = wp_json_encode( $data );
    if ( false === $json )
        return false;

    return base64_encode( $json );
}

/**
 * Decodes the given string with base64.
 *
 * @since 1.0
 *
 * @param string $string    string to decode from base64.
 *
 * @return mixed|false Decoded variable (usually an array or object), or false if it cannot be decoded.
 */
function usp_decode( $string ) {
    $decode = base64_decode( $string );
    if ( false === $decode )
        return false;

    return json_decode( $decode );
}
