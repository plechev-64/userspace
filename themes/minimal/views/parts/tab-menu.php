<?php
/**
 * Шаблон для рендеринга меню вкладок.
 *
 * @var AbstractTab[] $tabs_to_render Массив объектов вкладок для отображения.
 * @var bool $activate_first Активировать первую вкладку.
 * @var string $location Расположение меню.
 */

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

if (!defined('ABSPATH')) {
    exit;
}

$first = true;
foreach ($tabs_to_render as $tab) :
    $active_class = ($activate_first && $first) ? 'active' : '';
    $first = false;
    ?>
    <a href="#<?php echo esc_attr($tab->getId()); ?>"
       class="<?php echo esc_attr($active_class); ?>"
       data-tab-id="<?php echo esc_attr($tab->getId()); ?>"
       data-tab-location="<?php echo esc_attr($location); ?>">
        <?php echo esc_html($tab->getTitle()); ?>
    </a>
    <?php if (!empty($tab->getChildren())) : ?>
        <div class="usp-account-submenu">
            <?php foreach ($tab->getChildren() as $child_tab) : ?>
                <a href="#<?php echo esc_attr($child_tab->getId()); ?>"
                   data-tab-id="<?php echo esc_attr($child_tab->getId()); ?>"
                   data-tab-location="<?php echo esc_attr($location); ?>"><?php echo esc_html($child_tab->getTitle()); ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>