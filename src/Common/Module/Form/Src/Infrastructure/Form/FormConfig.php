<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Form;

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

    private array $config = [
        'sections' => [],
    ];

    private ?int $currentSectionIndex = null;
    private ?int $currentBlockIndex = null;

    /**
     * Добавляет новую секцию в конфигурацию.
     *
     * @param string $title Заголовок секции.
     *
     * @return self
     */
    public function addSection(string $title): self
    {
        $this->config['sections'][] = [
            'title' => $title,
            'blocks' => [],
        ];
        $this->currentSectionIndex = count($this->config['sections']) - 1;
        $this->currentBlockIndex = null; // Сбрасываем индекс блока при добавлении новой секции

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
        if ($this->currentSectionIndex === null) {
            throw new LogicException('Cannot add a block without a section. Call addSection() first.');
        }

        $this->config['sections'][$this->currentSectionIndex]['blocks'][] = [
            'title' => $title,
            'fields' => [],
        ];
        $this->currentBlockIndex = count($this->config['sections'][$this->currentSectionIndex]['blocks']) - 1;

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
        if ($this->currentBlockIndex === null) {
            throw new LogicException('Cannot add a field without a block. Call addBlock() first.');
        }

        $this->config['sections'][$this->currentSectionIndex]['blocks'][$this->currentBlockIndex]['fields'][$name] = $fieldConfig;

        return $this;
    }

    /**
     * Возвращает конфигурацию в виде массива.
     */
    public function toArray(): array
    {
        return $this->config;
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
        foreach ($this->config['sections'] as &$section) {
            foreach ($section['blocks'] as &$block) {
                if (isset($block['fields'][$fieldName])) {
                    $block['fields'][$fieldName]['value'] = $value;

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
        foreach ($this->config['sections'] as &$section) {
            foreach ($section['blocks'] as &$block) {
                if (isset($block['fields'][$fieldName])) {
                    unset($block['fields'][$fieldName]);

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
        foreach ($this->config['sections'] as $section) {
            foreach ($section['blocks'] as $block) {
                if (!empty($block['fields'])) {
                    $allFields = array_merge($allFields, $block['fields']);
                }
            }
        }

        return $allFields;
    }
}