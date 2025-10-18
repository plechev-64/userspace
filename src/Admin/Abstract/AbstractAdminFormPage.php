<?php

namespace UserSpace\Admin\Abstract;

use UserSpace\Form\FormConfig;
use UserSpace\Form\FormConfigBuilder;
use UserSpace\Form\FormManager;

/**
 * Абстрактный базовый класс для страниц конструкторов форм.
 */
abstract class AbstractAdminFormPage extends AbstractAdminPage
{
    protected readonly \UserSpace\Form\FieldMapper $fieldMapper;

    public function __construct(
        protected readonly FormManager       $formManager,
        protected readonly FormConfigBuilder $formBuilder,
        \UserSpace\Form\FieldMapper $fieldMapper
    ) {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * Регистрирует страницу как подменю.
     * @param string $parentSlug
     */
    protected function getParentSlug(): ?string
    {
        return 'userspace-settings';
    }

    /**
     * Подключает CSS и JS для страницы конструктора.
     * @param string $hook Текущий hook страницы.
     */
    final public function enqueueAssets(string $hook): void
    {
        if ($this->hookSuffix !== $hook) {
            return;
        }

        wp_enqueue_style(
            'usp-form-builder',
            USERSPACE_PLUGIN_URL . 'assets/css/form-builder.css',
            [],
            USERSPACE_VERSION
        );

        wp_enqueue_script(
            'sortable-js',
            'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'usp-form-builder-js',
            USERSPACE_PLUGIN_URL . 'assets/js/form-builder.js',
            ['usp-core', 'sortable-js'],
            USERSPACE_VERSION,
            true
        );

        wp_enqueue_style(
            'usp-form',
            USERSPACE_PLUGIN_URL . 'assets/css/form.css',
            [],
            USERSPACE_VERSION
        );

        wp_localize_script(
            'usp-core', // Привязываем настройки к нашему новому ядру
            'uspApiSettings',
            [
                'root' => esc_url_raw(rest_url()),
                'namespace' => USERSPACE_REST_NAMESPACE,
                'nonce' => wp_create_nonce('wp_rest'),
                'formType' => $this->getFormType(),
                'fieldTypes' => $this->fieldMapper->getMap(), // Это остается здесь, так как нужно только в билдере
            ]
        );

        // Локализация для конструктора форм
        wp_localize_script(
            'usp-form-builder-js',
            'uspL10n',
            [
                'formBuilder' => [
                    'confirmDeleteSectionWithCustom' => __('This section contains custom fields. Deleting the section will permanently remove all data for these fields from all users. Are you sure?', 'usp'),
                    'confirmDeleteBlockWithCustom' => __('This block contains custom fields. Deleting the block will permanently remove all data for these fields from all users. Are you sure?', 'usp'),
                    'confirmMoveFields' => __('This section contains fields. They will be moved to "Available Fields". Are you sure?', 'usp'),
                    'confirmDeleteCustomField' => __('This is a custom field. Deleting it will permanently remove all its data from all users. Are you sure?', 'usp'),
                    'errorPrefix' => __('Error: ', 'usp'),
                    'fieldSettingsTitle' => __('Field Settings:', 'usp'),
                    'fieldNameLabel' => __('Name (read-only)', 'usp'),
                    'cancel' => __('Cancel', 'usp'),
                    'save' => __('Save', 'usp'),
                    'close' => __('Close', 'usp'),
                    'createFieldTitle' => __('Create New Field', 'usp'),
                    'newFieldNameLabel' => __('Name (unique identifier)', 'usp'),
                    'newFieldTypeLabel' => __('Field Type', 'usp'),
                    'selectType' => __('-- Select type --', 'usp'),
                    'createFieldButton' => __('Create Field', 'usp'),
                    'loading' => __('Loading...', 'usp'),
                    'settingsLoadError' => __('Error loading settings', 'usp'),
                    'nameAndTypeRequired' => __('Field name and type are required.', 'usp'),
                    'kvValuePlaceholder' => __('Value', 'usp'),
                    'kvLabelPlaceholder' => __('Label', 'usp'),
                    'remove' => __('Remove', 'usp'),
                    'saving' => __('Saving...', 'usp'),
                    'unknownError' => __('Unknown error', 'usp'),
                ],
            ]
        );
    }

    /**
     * Рендерит содержимое страницы.
     */
    final public function render(): void
    {
        $formType = $this->getFormType();
        $config = $this->formManager->load($formType);

        if (null === $config) {
            $config = $this->createDefaultConfig();
            $this->formManager->save($formType, $config);
        }

        $defaultFields = $this->getFieldsFromConfig($this->createDefaultConfig());
        $currentFields = $this->getFieldsFromConfig($config);
        $availableFields = array_diff_key($defaultFields, $currentFields);

        $this->formBuilder->setAvailableFields($availableFields);

        $builderHtml = $this->formBuilder->load($config)->render();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->getPageTitle()) . '</h1>';
        echo '<div id="usp-form-builder-notifications"></div>';
        echo $builderHtml;
        echo '<p class="submit">';
        echo '<button type="button" id="usp-save-form-builder" class="button button-primary">' . __('Save Changes', 'usp') . '</button>';
        echo '</p>';

        // Подключаем скрытые HTML-шаблоны для JavaScript
        require_once USERSPACE_PLUGIN_DIR . 'views/admin/form-builder-templates.php';

        echo '</div>';
    }

    /**
     * Извлекает плоский список полей из конфигурации.
     * @param FormConfig $formConfig
     * @return array
     */
    protected function getFieldsFromConfig(FormConfig $formConfig): array
    {
        $fields = [];
        $configArray = $formConfig->toArray();
        if (empty($configArray['sections'])) {
            return [];
        }
        foreach ($configArray['sections'] as $section) {
            foreach ($section['blocks'] as $block) {
                $fields = array_merge($fields, $block['fields'] ?? []);
            }
        }
        return $fields;
    }
    abstract protected function getFormType(): string;

    abstract protected function createDefaultConfig(): FormConfig;
}