<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

use InvalidArgumentException;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Конструктор для программного создания конфигурации формы.
 */
class FormConfigBuilder
{

    private array $config = [
        'sections' => [],
    ];

    private ?string $currentSectionId = null;
    private ?string $currentBlockId = null;
    private array $availableFields = [];

    /**
     * Добавляет новую секцию в конфигурацию.
     *
     * @param string $id Уникальный идентификатор секции.
     * @param string|null $title Заголовок секции.
     *
     * @return $this
     */
    public function addSection(string $id, ?string $title = null): self
    {
        if (isset($this->config['sections'][$id])) {
            throw new InvalidArgumentException("Секция с ID '{$id}' уже существует.");
        }

        $this->config['sections'][$id] = [
            'title' => $title,
            'blocks' => [],
        ];

        $this->currentSectionId = $id;
        $this->currentBlockId = null;

        return $this;
    }

    /**
     * Добавляет новый блок в текущую секцию.
     *
     * @param string $id Уникальный идентификатор блока.
     * @param string|null $title Заголовок блока.
     *
     * @return $this
     */
    public function addBlock(string $id, ?string $title = null): self
    {
        if (null === $this->currentSectionId) {
            throw new InvalidArgumentException('Необходимо сначала добавить секцию с помощью addSection().');
        }

        if (isset($this->config['sections'][$this->currentSectionId]['blocks'][$id])) {
            throw new InvalidArgumentException("Блок с ID '{$id}' уже существует в текущей секции.");
        }

        $this->config['sections'][$this->currentSectionId]['blocks'][$id] = [
            'title' => $title,
            'fields' => [],
        ];

        $this->currentBlockId = $id;

        return $this;
    }

    /**
     * Добавляет поле в текущий блок.
     *
     * @param string $name Имя поля (ключ).
     * @param array $fieldConfig Конфигурация поля ('type', 'label', 'rules' и т.д.).
     *
     * @return $this
     */
    public function addField(string $name, array $fieldConfig): self
    {
        if (null === $this->currentBlockId) {
            throw new InvalidArgumentException('Необходимо сначала добавить блок с помощью addBlock().');
        }

        $this->config['sections'][$this->currentSectionId]['blocks'][$this->currentBlockId]['fields'][$name] = $fieldConfig;

        return $this;
    }

    /**
     * Возвращает собранный конфигурационный DTO.
     *
     * @return FormConfig
     */
    public function build(): FormConfig
    {
        $formConfig = new FormConfig();

        foreach ($this->config['sections'] as $section) {
            $formConfig->addSection($section['title'] ?? '');
            foreach ($section['blocks'] as $block) {
                $formConfig->addBlock($block['title'] ?? '');
                foreach ($block['fields'] as $name => $fieldConfig) {
                    $formConfig->addField($name, $fieldConfig);
                }
            }
        }

        return $formConfig;
    }

    /**
     * Устанавливает список доступных полей для отображения в отдельной панели.
     *
     * @param array $fields
     */
    public function setAvailableFields(array $fields): void
    {
        $this->availableFields = $fields;
    }

    /**
     * Сбрасывает внутреннее состояние конструктора.
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->config = ['sections' => []];
        $this->currentSectionId = null;
        $this->currentBlockId = null;
        return $this;
    }

    /**
     * Загружает существующую конфигурацию для редактирования.
     *
     * @param FormConfig $formConfig Конфигурация для загрузки.
     *
     * @return $this
     */
    public function load(FormConfig $formConfig): self
    {
        // Сбрасываем текущую конфигурацию
        $this->config = ['sections' => []];
        $this->currentSectionId = null;
        $this->currentBlockId = null;

        $configArray = $formConfig->toArray();

        if (empty($configArray['sections'])) {
            return $this;
        }

        // Восстанавливаем структуру с ID для редактирования
        foreach ($configArray['sections'] as $section_index => $section) {
            $section_id = $section['id'] ?? 'section-' . $section_index;
            $this->addSection($section_id, $section['title'] ?? '');

            if (empty($section['blocks'])) {
                continue;
            }

            foreach ($section['blocks'] as $block_index => $blockData) {
                $block_id = $blockData['id'] ?? 'block-' . $block_index;
                $this->addBlock($block_id, $blockData['title'] ?? '');

                if (empty($blockData['fields'])) {
                    continue;
                }

                foreach ($blockData['fields'] as $name => $fieldConfig) {
                    $this->addField($name, $fieldConfig);
                }
            }
        }

        return $this;
    }

    /**
     * Генерирует HTML-представление конструктора форм.
     *
     * @return string
     */
    public function render(): string
    {
        $output = '<div class="usp-form-config-builder" data-usp-form-builder>';

        $output .= '<div class="usp-form-builder-sections" data-sortable="sections">';
        foreach ($this->config['sections'] as $section_id => $section) {
            $output .= $this->renderSection($section_id, $section);
        }
        $output .= '</div>'; // .form-builder-sections

        $output .= $this->renderAvailableFieldsPanel();

        $output .= '<div class="usp-form-builder-main-actions"><button type="button" class="button button-primary" data-action="add-section">Добавить секцию</button></div>';
        $output .= '</div>';

        return $output;
    }

    private function renderSection(string $id, array $section): string
    {
        $output = sprintf('<div class="usp-form-builder-section" data-id="%s">', esc_attr($id));
        $output .= '<div class="usp-form-builder-section-header">
						<h3 class="usp-form-builder-section-title"><input type="text" class="title-input" value="%s" placeholder="%s" /></h3>
						<div class="usp-form-builder-section-actions">
							<button type="button" class="button" data-action="add-block">' . esc_html__('Add Block', 'usp') . '</button>
							<button type="button" class="button usp-button-link-delete" data-action="delete-section">' . esc_html__('Delete', 'usp') . '</button>
						</div>
					</div>';
        $output = sprintf($output, esc_attr($section['title'] ?? ''), esc_attr__('Untitled Section', 'usp'));
        $output .= '<div class="usp-form-builder-blocks" data-sortable="blocks">';

        foreach ($section['blocks'] as $block_id => $block) {
            $output .= $this->renderBlock($block_id, $block);
        }

        $output .= '</div></div>';

        return $output;
    }

    private function renderBlock(string $id, array $block): string
    {
        $output = sprintf('<div class="usp-form-builder-block" data-id="%s">', esc_attr($id));
        $output .= '<div class="usp-form-builder-block-header">
						<h4 class="usp-form-builder-block-title"><input type="text" class="title-input" value="%s" placeholder="%s" /></h4>
						<div class="usp-form-builder-block-actions">
							<button type="button" class="button usp-button-link-delete" data-action="delete-block">' . esc_html__('Delete', 'usp') . '</button>
						</div>
					</div>';
        $output = sprintf($output, esc_attr($block['title'] ?? ''), esc_attr__('Untitled Block', 'usp'));
        $output .= '<div class="usp-form-builder-fields" data-sortable="fields">';

        foreach ($block['fields'] as $name => $fieldConfig) {
            $output .= $this->renderField($name, $fieldConfig);
        }

        $output .= '</div>'; // .usp-form-builder-fields
        $output .= '<div class="usp-form-builder-block-footer"><button type="button" class="button button-secondary" data-action="add-custom-field">' . esc_html__('Add Field', 'usp') . '</button></div>';
        $output .= '</div>'; // .usp-form-builder-block

        return $output;
    }

    private function renderField(string $name, array $fieldConfig): string
    {
        $label = $fieldConfig['label'] ?? $name;
        $type = $fieldConfig['type'] ?? 'text';
        $config_json = wp_json_encode($fieldConfig);

        return sprintf(
            '<div class="usp-form-builder-field" data-name="%s" data-type="%s" data-config="%s">
				<span class="field-label">%s</span>
				<span class="field-type">[%s]</span>
				<div class="usp-form-builder-field-actions">
					<button type="button" class="button button-small" data-action="edit-field">' . esc_html__('Edit', 'usp') . '</button><button type="button" class="button button-small usp-button-link-delete" data-action="delete-field">' . esc_html__('Delete', 'usp') . '</button>
				</div>
			</div>',
            esc_attr($name),
            esc_attr($type),
            esc_attr($config_json),
            esc_html($label),
            esc_html($type)
        );
    }

    private function renderAvailableFieldsPanel(): string
    {
        if (empty($this->availableFields)) {
            return '';
        }

        $output = '<div class="usp-form-builder-available-fields">';
        $output .= '<h4>' . esc_html__('Available Fields', 'usp') . '</h4>';
        $output .= '<div class="usp-form-builder-fields" data-sortable="fields">';

        foreach ($this->availableFields as $name => $fieldConfig) {
            $output .= $this->renderField($name, $fieldConfig);
        }

        $output .= '</div></div>';

        return $output;
    }
}