<?php
/**
 * Шаблон формы авторизации.
 *
 * @package UserSpace
 */

use UserSpace\Common\Module\Settings\App\SettingsEnum;

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('usp_settings', []);
$redirect_to = $_REQUEST['redirect_to'] ?? home_url();
$redirectPageId = !empty($settings[SettingsEnum::REDIRECT_AFTER_LOGIN_PAGE_ID->value]) ? (int)$settings[SettingsEnum::REDIRECT_AFTER_LOGIN_PAGE_ID->value] : 0;
if ($redirectPageId > 0) {
    $pageUrl = get_permalink($redirectPageId);
    if ($pageUrl) {
        $redirect_to = $pageUrl;
    }
}
$registration_page_url = !empty($settings[SettingsEnum::REGISTRATION_PAGE_ID->value]) ? get_permalink($settings[SettingsEnum::REGISTRATION_PAGE_ID->value]) : wp_registration_url();
$lost_password_page_url = !empty($settings[SettingsEnum::PASSWORD_RESET_PAGE_ID->value]) ? get_permalink($settings[SettingsEnum::PASSWORD_RESET_PAGE_ID->value]) : wp_lostpassword_url();
?>

<form name="loginform" class="usp-form" id="loginform"
      action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post" data-usp-form="login">
    <div class="usp-form-wrapper">
        <div class="usp-form-field-wrapper">
            <label for="user_login"><?php _e('Username or Email Address', 'usp'); ?></label>
            <input type="text" name="log" id="user_login" class="input" value="" size="20"/>
        </div>
        <div class="usp-form-field-wrapper">
            <label for="user_pass"><?php _e('Password', 'usp'); ?></label>
            <input type="password" name="pwd" id="user_pass" class="input" value="" size="20"/>
        </div>
        <div class="usp-form-field-wrapper usp-form-field-rememberme">
            <label><input name="rememberme" type="checkbox" id="rememberme"
                          value="forever"> <?php _e('Remember Me', 'usp'); ?></label>
        </div>
        <div class="usp-form-submit-wrapper">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_to); ?>"/>
            <button type="submit" name="wp-submit" id="wp-submit"
                    class="button button-primary"><?php _e('Log In', 'usp'); ?></button>
        </div>
    </div>
</form>

<p id="nav">
    <?php if (get_option('users_can_register')) : ?>
        <a href="<?php echo esc_url($registration_page_url); ?>"><?php _e('Register', 'usp'); ?></a> |
    <?php endif; ?>
    <a href="<?php echo esc_url($lost_password_page_url); ?>"><?php _e('Lost your password?', 'usp'); ?></a>
</p>