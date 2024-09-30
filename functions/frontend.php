<?php

/**
 *  Load user account page.
 */
function userspace() {
	/**
	 * Fires from the top, on the account page
	 *
	 * @since   1.0.0
	 */
	do_action( 'usp_area_before' );
	?>

    <div id="usp-office" class="<?php echo esc_attr( usp_get_office_class() ); ?>"
         data-account="<?php echo esc_attr( USP()->office()->get_owner_id() ); ?>">

		<?php
		/**
		 * Fires for notifications on the account page.
		 *
		 * @since 1.0.0
		 */
		do_action( 'usp_area_notice' ); ?>

		<?php
		$themePath = USP()->theme()->get( 'path' );

		if ( $themePath ) {
			USP()->template( 'usp-office.php', $themePath )->include();
		} else {
			echo '<h3>' . esc_html__( 'Office templates not found!', 'userspace' ) . '</h3>';
		}
		?>

    </div>

	<?php
	/**
	 * Fires from the bottom, on the account page
	 *
	 * @since   1.0.0
	 */
	do_action( 'usp_area_after' );
}

// adding inline styles to the header
add_action( 'wp_head', 'usp_inline_styles', 100 );
function usp_inline_styles() {
	[ $r, $g, $b ] = sscanf( usp_get_option_customizer( 'usp_primary_color', '#0369a1' ), "#%02x%02x%02x" );

	/**
	 * Adding inline styles to the header.
	 *
	 * @param   $styles string  CSS ruleset.
	 * @param   $rgb    array   Array of rgb primary colors:
	 * <pre>
	 * $rgb['r']    int red.
	 * $rgb['g']    int green.
	 * $rgb['b']    int blue.
	 * </pre>
	 *
	 * @since   1.0.0
	 */
	$inline_styles = apply_filters( 'usp_inline_styles', '', [ $r, $g, $b ] );

	if ( ! $inline_styles ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "<style>" . usp_clearing_css( $inline_styles ) . "</style>\r\n";
}

// adding inline styles to the footer (if non-critical css)
add_action( 'wp_footer', 'usp_inline_styles_footer', 300 );
function usp_inline_styles_footer() {
	/**
	 * Adding inline styles to the footer.
	 *
	 * @param   $styles string  CSS ruleset.
	 *
	 * @since   1.0.0
	 */
	$inline_styles = apply_filters( 'usp_inline_styles_footer', '' );

	if ( ! $inline_styles ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "<style>" . usp_clearing_css( $inline_styles ) . "</style>\r\n";
}

/**
 * Clearing spaces, tabs, hyphenation etc.
 *
 * @param   $styles string  CSS ruleset.
 *
 * @return  string  Cleared CSS
 *
 * @since   1.0.0
 */
function usp_clearing_css( $styles ) {
	// removing spaces, hyphenation, and tabs
	$src_cleared = preg_replace( '/ {2,}/', '', str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $styles ) );

	// space : and {
	return str_replace( [ ': ', ' {' ], [ ':', '{' ], $src_cleared );
}

// size button api
add_filter( 'usp_inline_styles', 'usp_api_button_inline_size', 10 );
function usp_api_button_inline_size( $styles ) {
	$size = usp_get_option_customizer( 'usp_bttn_size', '16' );

	$styles .= '
		.usp-bttn__size-small > * {
			font-size: ' . round( 0.86 * $size ) . 'px;
		}
		.usp-bttn__size-standard > * {
			font-size: ' . $size . 'px;
		}
		.usp-bttn__size-medium:not(.usp-bttn__type-clear):not(.usp-bttn__mod-only-icon) > * {
			font-size: ' . round( 1.14 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-medium > *,
		.usp-bttn__size-large:not(.usp-bttn__mod-only-icon) > * {
			font-size: ' . round( 1.28 * $size ) . 'px;
		}
		.usp-bttn__size-big > * {
			font-size: ' . round( 1.5 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-large > * {
			font-size: ' . round( 1.8 * $size ) . 'px;
		}
		.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-big > * {
			font-size: ' . round( 2.1 * $size ) . 'px;
		}
	';

	return $styles;
}

// set frontend root inline css colors
add_filter( 'usp_inline_styles', 'usp_css_variable', 10 );
function usp_css_variable( $styles ) {
	$styles .= usp_get_root_colors();

	return $styles;
}

// Registers two areas: top and bottom in content. Allows you to add buttons.
add_filter( 'the_content', 'usp_before_post', 999 );
function usp_before_post( $content ) {
	if ( doing_filter( 'get_the_excerpt' ) || ! is_single() || is_front_page() ) {
		return $content;
	}

	/**
	 * Adding buttons before the content.
	 *
	 * @param string  Added buttons before the content.
	 *                  Default: empty string
	 *
	 * @since   1.0.0
	 */
	$before_post = apply_filters( 'usp_before_content_buttons', '' );

	$before = '<div id="usp-top-post-bttns" class="usp-top-post-bttns usp-post-bttns usps usps__jc-end">' . $before_post . '</div>';

	/**
	 * Adding buttons after the content.
	 *
	 * @param string  Added buttons after the content.
	 *                  Default: empty string
	 *
	 * @since   1.0.0
	 */
	$after_post = apply_filters( 'usp_after_content_buttons', '' );

	$after = '<div id="usp-bottom-post-bttns" class="usp-bottom-post-bttns usp-post-bttns usps usps__jc-end">' . $after_post . '</div>';

	return $before . $content . $after;
}

// Added js hook
add_action( 'wp_footer', 'usp_init_footer_action', 100 );
function usp_init_footer_action() {
	echo '<script>usp_do_action("usp_footer")</script>';
}

// Plugin overlay
add_action( 'wp_footer', 'usp_overlay_container', 4 );
function usp_overlay_container() {
	echo '<div id="usp-overlay"></div>';
}

/**
 * Catch logout command
 * ?usp-logout=1
 *
 * @return void redirect on home page.
 *
 * @since 1.0.0
 */
add_action( 'init', 'usp_wait_logout_get' );
function usp_wait_logout_get() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['usp-logout'] ) && ( '1' == $_GET['usp-logout'] ) ) {
		/**
		 * Filter allows you to change the logout URL.
		 *
		 * @param   $redirect   string  Path to redirect to on logout.
		 *                              Default: home page.
		 *
		 * @since   1.0.0
		 */
		$url = apply_filters( 'usp_logout_url_redirect', get_home_url() );

		wp_logout();

		wp_safe_redirect( esc_url( $url ) );
		exit;
	}
}

// Remove WordPress emoticons and replace them with a list of emojis
add_action( 'init', 'usp_global_emoji', 10 );
function usp_global_emoji() {
	if ( ! is_user_logged_in() || usp_get_option( 'usp_emoji', 1 ) == 0 ) {
		return false;
	}

	global $wpsmiliestrans;

	$wpsmiliestrans = []; // remove WP smilies

	// http://www.unicode.org/emoji/charts/full-emoji-list.html
	// https://emojipedia.org/twitter/

	/*
	 *  Note!
	 *  Don't delete smilies shortcodes - as an example :smile: :wink: e.t.c
	 *  They can be in your posts or comments, and then you will get "PHP Notice:  Undefined index: :mrgreen:"
	 */

	$smilies[':-)']       = "&#x1f642;";
	$smilies[':)']        = "&#x1f642;";
	$smilies[':smile:']   = "&#x1f642;";     // 🙂
	$smilies[';-)']       = "&#x1f609;";
	$smilies[';)']        = "&#x1f609;";
	$smilies[':wink:']    = "&#x1f609;";     // 😉
	$smilies[':-D']       = "&#x1f600;";
	$smilies[':D']        = "&#x1f600;";
	$smilies[':grin:']    = "&#x1f600;";     // 😀
	$smilies['&#x1f601;'] = "&#x1f601;";     // 😁
	$smilies['&#x1f603;'] = "&#x1f603;";     // 😃
	$smilies['&#x1f604;'] = "&#x1f604;";     // 😄
	$smilies['&#x1f605;'] = "&#x1f605;";     // 😅
	$smilies['&#x1f606;'] = "&#x1f606;";     // 😆
	$smilies[':lol:']     = "&#x1f606;";
	$smilies['&#x1f602;'] = "&#x1f602;";     // 😂
	$smilies['&#x1f60b;'] = "&#x1f60b;";     // 😋
	$smilies[':-P']       = "&#x1f61b;";
	$smilies[':P']        = "&#x1f61b;";
	$smilies[':razz:']    = "&#x1f61b;";     // 😛
	$smilies['&#x1f61c;'] = "&#x1f61c;";     // 😜
	$smilies['&#x1f61d;'] = "&#x1f61d;";     // 😝
	$smilies[':oops:']    = "\xf0\x9f\x98\xb3";
	$smilies['&#x1f60a;'] = "&#x1f60a;";     // 😊
	$smilies['&#x1f618;'] = "&#x1f618;";     // 😘
	$smilies['&#x1f60d;'] = "&#x1f60d;";     // 😍
	$smilies['&#x1f970;'] = "&#x1f970;";     // 🥰
	$smilies['&#x1f929;'] = "&#x1f929;";     // 🤩
	$smilies['&#x1f973;'] = "&#x1f973;";     // 🥳
	$smilies['8-)']       = "&#x1f60e;";
	$smilies[':cool:']    = "&#x1f60e;";     // 😎
	$smilies['&#x1f920;'] = "&#x1f920;";     // 🤠
	$smilies[':|']        = "&#x1f610;";
	$smilies[':-|']       = "&#x1f610;";
	$smilies[':neutral:'] = "&#x1f610;";     // 😐
	$smilies[':roll:']    = "\xf0\x9f\x99\x84";
	$smilies['&#x1f61f;'] = "&#x1f61f;";     // 😟
	$smilies[':-?']       = "&#x1f615;";
	$smilies[':???:']     = "&#x1f615;";     // 😕
	$smilies[':-(']       = "&#x1f641;";
	$smilies[':(']        = "&#x1f641;";
	$smilies[':sad:']     = "&#x1f641;";     // 🙁
	$smilies[':cry:']     = "\xf0\x9f\x98\xa5";
	$smilies['&#x1f62d;'] = "&#x1f62d;";     // 😭
	$smilies['8O']        = "&#x1f62f;";
	$smilies['8-O']       = "&#x1f62f;";
	$smilies[':shock:']   = "&#x1f62f;";     // 😯
	$smilies['&#x1f62e;'] = "&#x1f62e;";     // 😮
	$smilies[':eek:']     = "&#x1f62e;";
	$smilies['&#x1F632;'] = "&#x1F632;";     // 😲
	$smilies['&#x1f635;'] = "&#x1f635;";     // 😵
	$smilies[':-o']       = "&#x1f616;";
	$smilies[':o']        = "&#x1f616;";
	$smilies['&#x1f620;'] = "&#x1f620;";     // 😠
	$smilies[':x']        = "&#x1f621;";
	$smilies[':-x']       = "&#x1f621;";
	$smilies[':mad:']     = "&#x1f621;";     // 😡
	$smilies['&#x1f92c;'] = "&#x1f92c;";     // 🤬
	$smilies[':evil:']    = "\xf0\x9f\x91\xbf";
	$smilies['&#x1f608;'] = "&#x1f608;";     // 😈
	$smilies[':twisted:'] = "&#x1f608;";
	$smilies[':mrgreen:'] = "mrgreen.png";
	$smilies['&#x1f92e;'] = "&#x1f92e;";     // 🤮
	$smilies['&#x1f628;'] = "&#x1f628;";     // 😨
	$smilies['&#x1f637;'] = "&#x1f637;";     // 😷
	$smilies['&#x1f912;'] = "&#x1f912;";     // 🤒
	$smilies['&#x1f910;'] = "&#x1f910;";     // 🤐
	$smilies['&#x1F92F;'] = "&#x1F92F;";     // 🤯

	$smilies['&#x1f914;'] = "&#x1f914;";     // 🤔
	$smilies['&#x1F92D;'] = "&#x1F92D;";     // 🤭
	$smilies['&#x1F631;'] = "&#x1F631;";     // 😱

	$smilies['&#x1f97a;'] = "&#x1f97a;";     // 🥺
	$smilies['&#x1f607;'] = "&#x1f607;";     // 😇
	$smilies['&#x1f62c;'] = "&#x1f62c;";     // 😬
	$smilies['&#x1f92b;'] = "&#x1f92b;";     // 🤫
	$smilies['&#x1f634;'] = "&#x1f634;";     // 😴

	$smilies['&#x1f4a9;'] = "&#x1f4a9;";     // 💩

	// events
	$smilies['&#x1f383;']        = "&#x1f383;";          // 🎃
	$smilies['&#x1f921;']        = "&#x1f921;";          // 🤡
	$smilies['&#x1f9d9;']        = "&#x1f9d9;";          // 🧙
	$smilies['&#x1f9da;']        = "&#x1f9da;";          // 🧚
	$smilies['&#x1F9DF;']        = "&#x1F9DF;";          // 🧟
	$smilies['&#x1f332;']        = "&#x1f332;";          // 🌲
	$smilies['&#x1f384;']        = "&#x1f384;";          // 🎄
	$smilies['&#x2744;']         = "&#x2744;";           // ❄
	$smilies['&#x2603;&#xfe0f;'] = "&#x2603;&#xfe0f;";   // ☃
	$smilies['&#x1f385;']        = "&#x1f385;";          // 🎅

	// transport
	$smilies['&#x1f697;'] = "&#x1f697;";     // 🚗
	$smilies['&#x1f69c;'] = "&#x1f69c;";     // 🚜
	$smilies['&#x1f682;'] = "&#x1f682;";     // 🚂
	$smilies['&#x1f681;'] = "&#x1f681;";     // 🚁
	$smilies['&#x2708;']  = "&#x2708;";      // ✈
	$smilies['&#x1f680;'] = "&#x1f680;";     // 🚀

	// drink
	$smilies['&#x1f37a;'] = "&#x1f37a;";     // 🍺
	$smilies['&#x1f37b;'] = "&#x1f37b;";     // 🍻
	$smilies['&#x1f377;'] = "&#x1f377;";     // 🍷
	$smilies['&#x1f942;'] = "&#x1f942;";     // 🥂
	$smilies['&#x1f379;'] = "&#x1f379;";     // 🍹
	$smilies['&#x1f378;'] = "&#x1f378;";     // 🍸
	$smilies['&#x2615;']  = "&#x2615;";      // ☕

	// food
	$smilies['&#x1f344;'] = "&#x1f344;";     // 🍄
	$smilies['&#x1f34c;'] = "&#x1f34c;";     // 🍌
	$smilies['&#x1f352;'] = "&#x1f352;";     // 🍒
	$smilies['&#x1f353;'] = "&#x1f353;";     // 🍓
	$smilies['&#x1f355;'] = "&#x1f355;";     // 🍕
	$smilies['&#x1F9C0;'] = "&#x1F9C0;";     // 🧀

	// emotions
	$smilies['&#x1f48b;'] = "&#x1f48b;";     // 💋
	$smilies['&#x2764;']  = "&#x2764;";      // ❤
	$smilies['&#x1f498;'] = "&#x1f498;";     // 💘
	$smilies['&#x1f495;'] = "&#x1f495;";     // 💕
	$smilies['&#x1f48c;'] = "&#x1f48c;";     // 💌
	$smilies['&#x1f494;'] = "&#x1f494;";     // 💔
	$smilies['&#x1F64F;'] = "&#x1F64F;";     // 🙏
	$smilies['&#x1F44F;'] = "&#x1F44F;";     // 👏
	$smilies['&#x270c;']  = "&#x270c;";      // ✌ victory
	$smilies['&#x1f44d;'] = "&#x1f44d;";     // 👍
	$smilies['&#x1f44e;'] = "&#x1f44e;";     // 👎
	$smilies['&#x1f91d;'] = "&#x1f91d;";     // 🤝

	// symbols
	$smilies[':arrow:']   = "\xe2\x9e\xa1";
	$smilies['&#x2795;']  = "&#x2795;";      // ➕
	$smilies['&#x2714;']  = "&#x2714;";      // ✔
	$smilies['&#x2716;']  = "&#x2716;";      // ✖
	$smilies['&#x2753;']  = "&#x2753;";      // ❓
	$smilies['&#x2757;']  = "&#x2757;";      // ❗
	$smilies['&#x26a0;']  = "&#x26a0;";      // ⚠
	$smilies['&#x26a1;']  = "&#x26a1;";      // ⚡
	$smilies['&#x1f4cc;'] = "&#x1f4cc;";     // 📌
	$smilies['&#x1f6ab;'] = "&#x1f6ab;";     // 🚫
	$smilies['&#x1F5D1;'] = "&#x1F5D1;";     // 🗑
	$smilies['&#x1F50e;'] = "&#x1F50e;";     // 🔎
	$smilies['&#x1f4a1;'] = "&#x1f4a1;";     // 💡
	$smilies[':idea:']    = "&#x1f4a1;";
	$smilies['&#x1f4a3;'] = "&#x1f4a3;";     // 💣
	$smilies['&#x2728;']  = "&#x2728;";      // ✨
	$smilies['&#x1f4a5;'] = "&#x1f4a5;";     // 💥
	$smilies['&#x1F525;'] = "&#x1F525;";     // 🔥
	$smilies['&#x1F389;'] = "&#x1F389;";     // 🎉
	$smilies['&#x1f381;'] = "&#x1f381;";     // 🎁
	$smilies['&#x1F382;'] = "&#x1F382;";     // 🎂
	$smilies['&#x1f4b0;'] = "&#x1f4b0;";     // 💰
	$smilies['&#x1f4b5;'] = "&#x1f4b5;";     // 💵
	$smilies['&#x1f4b2;'] = "&#x1f4b2;";     // 💲
	$smilies['&#x270f;']  = "&#x270f;";      // ✏
	$smilies['&#x1f4dd;'] = "&#x1f4dd;";     // 📝
	$smilies['&#x1F517;'] = "&#x1F517;";     // 🔗
	$smilies['&#x1f528;'] = "&#x1f528;";     // 🔨
	$smilies['&#x1f527;'] = "&#x1f527;";     // 🔧
	$smilies['&#x2699;']  = "&#x2699;";      // ⚙
	$smilies['&#x1f552;'] = "&#x1f552;";     // 🕒
	$smilies['&#x1f4a4;'] = "&#x1f4a4;";     // 💤

	$smilies['&#x1f1f7;&#x1f1fa;'] = "&#x1f1f7;&#x1f1fa;";   // ru flag
	$smilies['&#x1F1FA;&#x1F1F8;'] = "&#x1F1FA;&#x1F1F8;";   // usa flag
	$smilies['&#x2709;']           = "&#x2709;";             // ✉
	$smilies['&#x1f3c6;']          = "&#x1f3c6;";            // 🏆
	$smilies['&#x1F3C5;']          = "&#x1F3C5;";            // 🏅

	// animals
	$smilies['&#x1f425;'] = "&#x1f425;";     // 🐥
	$smilies['&#x1f41f;'] = "&#x1f41f;";     // 🐟
	$smilies['&#x1f437;'] = "&#x1f437;";     // 🐷
	$smilies['&#x1f41e;'] = "&#x1f41e;";     // 🐞
	$smilies['&#x1f577;'] = "&#x1f577;";     // 🕷
	$smilies['&#x1F440;'] = "&#x1F440;";     // 👀
	$smilies['&#x1f47d;'] = "&#x1f47d;";     // 👽
	$smilies['&#x1f480;'] = "&#x1f480;";     // 💀
	$smilies['&#x1f47b;'] = "&#x1f47b;";     // 👻

	$smilies['&#x2601;']          = "&#x2601;";             // ☁
	$smilies['&#x1f327;&#xfe0f;'] = "&#x1f327;&#xfe0f;";    // 🌧
	$smilies['&#x26c5;']          = "&#x26c5;";             // ⛅
	$smilies['&#x2600;']          = "&#x2600;";             // ☀
	$smilies['&#x1f334;']         = "&#x1f334;";            // 🌴
	$smilies['&#x1f33c;']         = "&#x1f33c;";            // 🌼
	$smilies['&#x1f490;']         = "&#x1f490;";            // 💐
	$smilies['&#x1f341;']         = "&#x1f341;";            // 🍁
	$smilies['&#x1f342;']         = "&#x1f342;";            // 🍂

	$smilies['&#x26fa;']  = "&#x26fa;";     // ⛺
	$smilies['&#x23f0;']  = "&#x23f0;";     // ⏰
	$smilies['&#x26bd;']  = "&#x26bd;";     // ⚽
	$smilies['&#x2b50;']  = "&#x2b50;";     // ⭐
	$smilies['&#x1f4af;'] = "&#x1f4af;";    // 💯
	$smilies['&#x1F4AC;'] = "&#x1F4AC;";    // 💬

	/** @noinspection PhpUnnecessaryLocalVariableInspection */
	/**
	 * Emoji array filter.
	 *
	 * @param   $smilies    array   List of emoji.
	 *
	 * @since   1.0.0
	 */
	$wpsmiliestrans = apply_filters( 'usp_emoji_list', $smilies );

	return $wpsmiliestrans;
}
