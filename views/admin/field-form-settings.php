<?php
/**
 * Шаблон для модального окна с формой настроек.
 *
 * @var \UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface $form Объект формы для рендеринга.
 * @var StringFilterInterface $str Сервис для локализации.
 */

use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Core\String\StringFilterInterface;

?>
<form class="usp-form">
    <?php echo $form->render(); ?>
</form>
