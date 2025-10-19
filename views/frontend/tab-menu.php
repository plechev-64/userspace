<?php
/**
 * Шаблон для рендеринга меню вкладок.
 *
 * @package UserSpace
 *
 * @var \UserSpace\Common\Module\Tabs\Src\Domain\TabDto[] $tabs_to_render Массив вкладок для отображения.
 * @var bool                          $is_first_group Флаг, указывающий, является ли это первой группой вкладок на странице.
 */

if (!defined('ABSPATH') || empty($tabs_to_render)) {
	exit;
}
?>
<ul class="usp-account-menu">
	<?php foreach ($tabs_to_render as $index => $tab) :
		$is_first_parent_in_group = ($is_first_group && $index === 0);
		$parent_active_class      = $is_first_parent_in_group ? 'is-active' : '';
		$has_submenu_class        = !empty($tab->subTabs) ? 'has-submenu' : '';

		// Если у родительской вкладки нет своего контента, ссылка ведет на первую дочернюю вкладку.
		$parent_href = empty($tab->contentSource) && !empty($tab->subTabs)
			? '#' . esc_attr($tab->subTabs[0]->id)
			: '#' . esc_attr($tab->id);
		?>
        <li class="usp-account-menu-item <?php echo esc_attr($has_submenu_class . ' ' . $parent_active_class); ?>">
            <a href="<?php echo esc_url($parent_href); ?>"
               class="<?php echo ($is_first_parent_in_group && empty($tab->subTabs)) ? 'active' : ''; ?>">
				<?php if ($tab->icon) : ?><span class="dashicons <?php echo esc_attr($tab->icon); ?>"></span><?php endif; ?>
				<?php echo esc_html($tab->title); ?>
            </a>
			<?php if (!empty($tab->subTabs)) : ?>
                <ul class="usp-account-submenu">
					<?php foreach ($tab->subTabs as $sub_index => $subTab) :
						// Делаем активной только самую первую подвкладку в самом первом родительском пункте.
						$is_first_sub_tab = ($is_first_parent_in_group && $sub_index === 0);
						?>
                        <li class="usp-account-submenu-item">
                            <a href="#<?php echo esc_attr($subTab->id); ?>" class="<?php echo $is_first_sub_tab ? 'active' : ''; ?>">
								<?php echo esc_html($subTab->title); ?>
                            </a>
                        </li>
					<?php endforeach; ?>
                </ul>
			<?php endif; ?>
        </li>
	<?php endforeach; ?>
</ul>