<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Factory;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\Factory\FieldFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Factory\FormFactoryInterface;
use UserSpace\Common\Module\Form\Src\Domain\Form\FormInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Block;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Form;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\Form\Section;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Фабрика для создания объектов Form.
 */
class FormFactory implements FormFactoryInterface
{
    public function __construct(
        private readonly FieldFactoryInterface $fieldFactory
    )
    {
    }

    /**
     * Создает экземпляр формы на основе конфигурации.
     *
     * @param FormConfig $formConfig Конфигурация полей формы.
     *
     * @return FormInterface
     * @throws InvalidArgumentException Если указан неподдерживаемый тип поля.
     */
    public function create(FormConfig $formConfig): FormInterface
    {
        $config = $formConfig->toArray();
        $sections = [];
        $section_configs = $config['sections'] ?? [];

        foreach ($section_configs as $section_config) {
            $blocks = [];
            $block_configs = $section_config['blocks'] ?? [];

            foreach ($block_configs as $block_config) {
                $fields = [];
                $field_configs = $block_config['fields'] ?? [];

                foreach ($field_configs as $name => $fieldData) {
                    $fields[] = $this->fieldFactory->createFromConfig($name, $fieldData);
                }
                $blocks[] = new Block($block_config['title'] ?? '', $fields);
            }
            $sections[] = new Section($section_config['title'] ?? '', $blocks);
        }

        return new Form($sections);
    }
}