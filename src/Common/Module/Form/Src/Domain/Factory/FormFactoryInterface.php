<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Factory;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\FormConfig;

/**
 * Интерфейс для фабрики, создающей объекты Form.
 */
interface FormFactoryInterface
{
    /**
     * Создает экземпляр формы на основе конфигурации.
     *
     * @param FormConfig $formConfig Конфигурация полей формы.
     *
     * @return FormInterface
     * @throws InvalidArgumentException Если указан неподдерживаемый тип поля.
     */
    public function create(FormConfig $formConfig): FormInterface;
}