<?php
/**
 * Шаблон для рендеринга меню вкладок.
 *
 * @package UserSpace
 *
 * @var AbstractTab[] $tabs_to_render,
 * @var bool $activate_first,
 * @var string $location,
 */

use UserSpace\Common\Module\Tabs\Src\Domain\AbstractTab;

if (!defined('ABSPATH') || empty($tabs_to_render)) {
	exit;
}
?>
<ul class="usp-account-menu">
	<?php foreach ($tabs_to_render as $index => $tab) :
		$is_first_parent_in_group = ($activate_first && $index === 0);
		$parent_active_class      = $is_first_parent_in_group ? 'is-active' : '';
		$has_submenu_class        = !empty($tab->getSubTabs()) ? 'has-submenu' : '';

		// Если у родительской вкладки нет своего контента, ссылка ведет на первую дочернюю вкладку.
		$parent_href = empty($tab->getContentSource()) && !empty($tab->getSubTabs())
			? '#' . esc_attr($tab->getSubTabs()[0]->getId())
			: '#' . esc_attr($tab->getId());
		?>
        <li class="usp-account-menu-item <?php echo esc_attr($has_submenu_class . ' ' . $parent_active_class); ?>">
            <a href="<?php echo esc_url($parent_href); ?>"
               class="<?php echo ($is_first_parent_in_group && empty($tab->getSubTabs())) ? 'active' : ''; ?>">
				<?php if ($tab->getIcon()) : ?><span class="dashicons <?php echo esc_attr($tab->getIcon()); ?>"></span><?php endif; ?>
				<?php echo esc_html($tab->getTitle()); ?>
            </a>
			<?php if (!empty($tab->getSubTabs())) : ?>
                <ul class="usp-account-submenu">
					<?php
                    /**
                     * @var int $sub_index
                     * @var AbstractTab $subTab
                     */
                    foreach ($tab->getSubTabs() as $sub_index => $subTab) :
						// Делаем активной только самую первую подвкладку в самом первом родительском пункте.
						$is_first_sub_tab = ($is_first_parent_in_group && $sub_index === 0);
						?>
                        <li class="usp-account-submenu-item">
                            <a href="#<?php echo esc_attr($subTab->getId()); ?>" class="<?php echo $is_first_sub_tab ? 'active' : ''; ?>">
								<?php echo esc_html($subTab->getTitle()); ?>
                            </a>
                        </li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </li>
	<?php endforeach; ?>
</ul>