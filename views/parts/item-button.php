<?php
// Защита от прямого доступа к файлу
use UserSpace\Common\Module\Locations\Src\Domain\ItemInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @var ItemInterface $item
 * @var string $title
 * @var string $actionEndpoint
 */
?>
<a class="usp-sidebar-nav__link usp-sidebar-nav__link--action" href="#" data-action-endpoint="<?php echo esc_url($actionEndpoint); ?>" role="button">
     <span class="usp-sidebar-nav__icon">
         <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
     </span>
    <span class="usp-sidebar-nav__title">
         <?php echo esc_html($title); ?>
     </span>
</a>