<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Form\Config;

use LogicException;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * DTO для конфигурации формы.
 * Использует текучий интерфейс для построения конфигурации.
 */
class FormConfig
{
    /**
     * @var SectionConfig[]
     */
    private array $sections = [];

    private ?SectionConfig $currentSection = null;
    private ?BlockConfig $currentBlock = null;

    /**
     * Добавляет новую секцию в конфигурацию.
     *
     * @param string $title Заголовок секции.
     *
     * @return self
     */
    public function addSection(string $title): self
    {
        $section = new SectionConfig($title);
        $this->sections[] = $section;
        $this->currentSection = $section;
        $this->currentBlock = null; // Сбрасываем текущий блок при добавлении новой секции

        return $this;
    }

    /**
     * Добавляет новый блок в текущую секцию.
     *
     * @param string $title Заголовок блока.
     *
     * @return self
     * @throws LogicException Если не была добавлена секция.
     */
    public function addBlock(string $title): self
    {
        if ($this->currentSection === null) {
            throw new LogicException('Cannot add a block without a section. Call addSection() first.');
        }

        $block = new BlockConfig($title);
        $this->currentSection->addBlock($block);
        $this->currentBlock = $block;

        return $this;
    }

    /**
     * Добавляет поле в текущий блок.
     *
     * @param string $name Имя поля.
     * @param array $fieldConfig Конфигурация поля.
     *
     * @return self
     * @throws LogicException Если не был добавлен блок.
     */
    public function addField(string $name, array $fieldConfig): self
    {
        if ($this->currentBlock === null) {
            throw new LogicException('Cannot add a field without a block. Call addBlock() first.');
        }

        $this->currentBlock->addField($name, $fieldConfig);

        return $this;
    }

    /**
     * Возвращает конфигурацию в виде массива.
     */
    public function toArray(): array
    {
        $sectionsArray = [];
        foreach ($this->sections as $section) {
            $sectionsArray[] = $section->toArray();
        }
        return [
            'sections' => $sectionsArray,
        ];
    }

    /**
     * Обновляет значение ('value') для указанного поля.
     *
     * @param string $fieldName Имя поля для обновления.
     * @param mixed $value Новое значение.
     *
     * @return bool True, если поле найдено и обновлено, иначе false.
     */
    public function updateFieldValue(string $fieldName, mixed $value): bool
    {
        foreach ($this->sections as $section) {
            foreach ($section->getBlocks() as $block) {
                if ($block->hasField($fieldName)) {
                    $block->updateFieldValue($fieldName, $value);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Удаляет поле из конфигурации.
     *
     * @param string $fieldName Имя поля для удаления.
     *
     * @return bool True, если поле найдено и удалено, иначе false.
     */
    public function removeField(string $fieldName): bool
    {
        foreach ($this->sections as $section) {
            foreach ($section->getBlocks() as $block) {
                if ($block->removeField($fieldName)) {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Создает экземпляр FormConfig из массива.
     *
     * @param array $configData Массив с конфигурацией.
     *
     * @return self
     */
    public static function fromArray(array $configData): self
    {
        $formConfig = new self();

        foreach ($configData['sections'] ?? [] as $sectionData) {
            $formConfig->addSection($sectionData['title'] ?? '');
            foreach ($sectionData['blocks'] ?? [] as $blockData) {
                $formConfig->addBlock($blockData['title'] ?? '');
                foreach ($blockData['fields'] ?? [] as $name => $fieldData) {
                    $formConfig->addField($name, $fieldData);
                }
            }
        }

        return $formConfig;
    }

    /**
     * Возвращает плоский список всех полей из конфигурации.
     *
     * @return array
     */
    public function getFields(): array
    {
        $allFields = [];
        foreach ($this->sections as $section) {
            foreach ($section->getBlocks() as $block) {
                if (!empty($block->getFields())) {
                    $allFields = array_merge($allFields, $block->getFields());
                }
            }
        }

        return $allFields;
    }

    /**
     * @return SectionConfig[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }
}