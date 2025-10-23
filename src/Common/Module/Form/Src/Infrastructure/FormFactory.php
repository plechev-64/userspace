<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

use InvalidArgumentException;
use UserSpace\Common\Module\Form\Src\Domain\FormInterface;
use UserSpace\Core\Container\ContainerInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Фабрика для создания объектов Form.
 */
class FormFactory
{

    private readonly FieldMapper $fieldMapper;
    private readonly ContainerInterface $container;

    public function __construct(FieldMapper $fieldMapper, ContainerInterface $container)
    {
        $this->fieldMapper = $fieldMapper;
        $this->container = $container;
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

                foreach ($field_configs as $name => $field_config) {
                    $type = $field_config['type'] ?? 'text';
                    $class_name = $this->fieldMapper->getClass($type);
                    $dtoClass = $this->fieldMapper->getDtoClass($type);

                    // Используем DI-контейнер для создания экземпляра поля, передавая DTO как аргумент.
                    // Контейнер автоматически внедрит остальные зависимости (StringFilter, SecurityHelperInterface и т.д.).
                    $fields[] = $this->container->build($class_name, ['dto' => new $dtoClass($name, $field_config)]);
                }
                $blocks[] = new Block($block_config['title'] ?? '', $fields);
            }
            $sections[] = new Section($section_config['title'] ?? '', $blocks);
        }

        return new Form($sections);
    }
}