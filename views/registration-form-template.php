<?php
/**
 * Шаблон формы регистрации.
 *
 * @var \UserSpace\Common\Module\Form\Src\Infrastructure\Form $form
 * @var array $settings
 * @package UserSpace
 */

if ( ! defined('ABSPATH')) {
    exit;
}

$login_page_url = !empty($settings['login_page_id']) ? get_permalink($settings['login_page_id']) : wp_login_url();
?>

<form name="registerform" class="usp-form" id="registerform" action="" method="post" data-usp-form="registration">
    <?php echo $form->render(); ?>

    <div class="usp-form-submit-wrapper">
        <button type="submit" name="wp-submit" id="wp-submit" class="button button-primary"><?php _e('Register', 'usp'); ?></button>
    </div>
</form>

<p id="nav">
    <a href="<?php echo esc_url($login_page_url); ?>"><?php _e('Log in', 'usp'); ?></a>
</p>