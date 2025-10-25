<?php
/**
 * Шаблон формы восстановления пароля.
 *
 * @var array $settings
 * @package UserSpace
 */

use UserSpace\Common\Module\Settings\App\SettingsEnum;

if (!defined('ABSPATH')) {
    exit;
}

$login_page_url = !empty($settings[SettingsEnum::LOGIN_PAGE_ID->value]) ? get_permalink($settings[SettingsEnum::LOGIN_PAGE_ID->value]) : wp_login_url();
$registration_page_url = !empty($settings[SettingsEnum::REGISTRATION_PAGE_ID->value]) ? get_permalink($settings[SettingsEnum::REGISTRATION_PAGE_ID->value]) : wp_registration_url();
?>

<form name="lostpasswordform" class="usp-form" id="lostpasswordform"
      action="<?php echo esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post')); ?>" method="post"
      data-usp-form="forgot-password">
    <p><?php _e('Please enter your username or email address. You will receive an email message with instructions on how to reset your password.', 'usp'); ?></p>
    <div class="usp-form-field-wrapper">
        <label for="user_login"><?php _e('Username or Email Address', 'usp'); ?></label>
        <input type="text" name="user_login" id="user_login" class="input" value="" size="20"/>
    </div>
    <div class="usp-form-submit-wrapper">
        <button type="submit" name="wp-submit" id="wp-submit"
                class="button button-primary"><?php _e('Get New Password', 'usp'); ?></button>
    </div>
</form>

<p id="nav">
    <a href="<?php echo esc_url($login_page_url); ?>"><?php _e('Log in', 'usp'); ?></a>
    <?php if (get_option('users_can_register')) : ?>
        | <a href="<?php echo esc_url($registration_page_url); ?>"><?php _e('Register', 'usp'); ?></a>
    <?php endif; ?>
</p>