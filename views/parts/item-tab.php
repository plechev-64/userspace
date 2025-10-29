<?php
// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Шаблон для одного элемента-вкладки в меню.
 *
 * @var ItemInterface $item
 * @var string $url
 * @var string $title
 * @var bool $is_active
 */
$active_class  = $is_active ? 'is-active' : '';
?>

<a class="usp-sidebar-nav__link <?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $url ); ?>">
    <span class="usp-sidebar-nav__icon">
        <!-- Иконка будет добавлена позже -->
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>
    </span>
    <span class="usp-sidebar-nav__title">
        <?php echo esc_html( $title ); ?>
    </span>
</a>