<?php
/**
 * Шаблон личного кабинета для темы "First".
 *
 * @var string $avatarBlock HTML-блок с аватаром пользователя.
 * @var string $headerMenu  HTML-меню для хедера.
 * @var string $sidebarMenu HTML-меню для сайдбара.
 * @var string $tabsContent HTML-контент вкладок.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="usp-account-wrapper">
    <div class="usp-account-header">
        <?php echo $avatarBlock; ?>
        <div class="usp-header-tabs">
            <div class="usp-account-menu">
				<?php echo $headerMenu; ?>
            </div>
        </div>
    </div>

    <div class="usp-account-sidebar">
        <div class="usp-account-menu">
			<?php echo $sidebarMenu; ?>
        </div>
    </div>
    <div class="usp-account-content">
        <?php echo $tabsContent; ?>
    </div>
</div>