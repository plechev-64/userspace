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
    $default_cover = USP_URL . 'themes/default/img/default-cover.jpg';

    if ( $avatar_cover && $user_id ) {
        $avatar = get_user_meta( $user_id, 'usp_avatar', 1 );
        if ( $avatar ) {
            $default_cover = get_avatar_url( $user_id, [ 'size' => 1000 ] );
        }
    }

    $current_id = usp_get_option( 'usp-current-office' );

    // default userspace theme user account
    if ( $current_id === 'userspace/themes/default/index.php' )
        return $default_cover;

    // other theme
    if ( in_array( $current_id, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        $file = dirname( plugins_url() . '/' . $current_id ) . '/img/default-cover.jpg';

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
    if ( ! usp_get_option( 'view_usp_bar' ) )
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

function usp_get_roles_ids() {
    $editable_roles = array_reverse( get_editable_roles() );
    $roles          = [];
    foreach ( $editable_roles as $role => $details ) {
        if ( $role == 'administrator' )
            continue;
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

function usp_get_smiles( $id_area ) {

    $smiles = '<div class="usp-smiles" data-area="' . $id_area . '">';
    $smiles .= '<i class="uspi fa-beaming-face-with-smiling-eyes" aria-hidden="true"></i>';
    $smiles .= '<div class="usp-smiles-list">
						<div class="smiles"></div>
					</div>';
    $smiles .= '</div>';

    return $smiles;
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

    add_filter( 'wp_mail_content_type', function() {
        return "text/html";
    } );

    $headers = 'From: ' . $from_name . ' <' . $from_mail . '>' . "\r\n";

    $content = usp_get_include_template( 'mail.php', false, [
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

function usp_get_balloon( $args ) {

    $content = '<span class="usp-balloon-hover ' . (isset( $args['class'] ) ? $args['class'] : '') . '">';
    $content .= '<i class="uspi ' . $args['icon'] . '" aria-hidden="true"></i>';
    if ( isset( $args['label'] ) ) {
        $content .= ' ' . $args['label'];
    }
    $content .= '<span class="usp-balloon ' . (isset( $args['position'] ) ? 'position-' . $args['position'] : 'position-bottom') . '">';
    $content .= $args['content'];
    $content .= '</span>';
    $content .= '</span>';

    return $content;
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

    if ( ! $force && ! usp_get_option( 'usp-log' ) )
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
