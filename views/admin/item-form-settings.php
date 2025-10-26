<?php
/**
 * Шаблон с формой настроек ItemInterface.
 *
 * @var FormInterface $form Объект формы для рендеринга.
 */

use UserSpace\Common\Module\Form\Src\Domain\FormInterface;
use UserSpace\Core\String\StringFilterInterface;

?>
<form class="usp-form">
    <?php echo $form->render(); ?>
</form>
