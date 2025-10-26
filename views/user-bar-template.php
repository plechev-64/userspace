<?php
/**
 * Шаблон для UserBar.
 *
 * @package UserSpace
 *
 * @var string $login_page_url
 * @var string $registration_page_url
 * @var string $account_page_url
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="usp-user-bar">
    <div class="usp-user-bar-inner">
        <ul class="usp-user-bar-menu">
            <?php if (is_user_logged_in()) :
                $user = wp_get_current_user();
                $logout_url = wp_logout_url(home_url());
                ?>
                <li>
                    <a href="<?php echo esc_url($account_page_url); ?>" class="usp-user-avatar">
                        <?php echo get_avatar($user->ID, 24); ?>
                        <span><?php echo esc_html($user->display_name); ?></span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url($logout_url); ?>"><?php _e('Log Out', 'usp'); ?></a>
                </li>
            <?php else : ?>
                <li>
                    <a href="<?php echo esc_url($login_page_url); ?>"><?php _e('Log In', 'usp'); ?></a>
                </li>
                <?php if (get_option('users_can_register')) : ?>
                    <li>
                        <a href="<?php echo esc_url($registration_page_url); ?>"><?php _e('Register', 'usp'); ?></a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>