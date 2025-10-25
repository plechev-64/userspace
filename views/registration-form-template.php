<?php
/**
 * Шаблон формы регистрации.
 *
 * @var \UserSpace\Common\Module\Form\Src\Infrastructure\Form $form
 * @var array $settings
 * @package UserSpace
 */

use UserSpace\Common\Module\Settings\App\SettingsEnum;

if (!defined('ABSPATH')) {
    exit;
}

$login_page_url = !empty($settings[SettingsEnum::LOGIN_PAGE_ID->value]) ? get_permalink($settings[SettingsEnum::LOGIN_PAGE_ID->value]) : wp_login_url();
?>

<form name="registerform" class="usp-form" id="registerform" action="" method="post" data-usp-form="registration">
    <?php echo $form->render(); ?>

    <div class="usp-form-submit-wrapper">
        <button type="submit" name="wp-submit" id="wp-submit"
                class="button button-primary"><?php _e('Register', 'usp'); ?></button>
    </div>
</form>

<p id="nav">
    <a href="<?php echo esc_url($login_page_url); ?>"><?php _e('Log in', 'usp'); ?></a>
</p>