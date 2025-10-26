<?php
/**
 * Шаблон с формой настроек ItemInterface.
 *
 * @var \UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface $form Объект формы для рендеринга.
 */

use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;

?>
<form class="usp-form">
    <?php echo $form->render(); ?>
</form>
