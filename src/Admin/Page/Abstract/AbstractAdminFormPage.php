<?php

namespace UserSpace\Admin\Page\Abstract;

use UserSpace\Common\Module\Form\Src\Infrastructure\FieldMapper;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfig;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormConfigBuilder;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TemplateManagerInterface;

/**
 * Абстрактный базовый класс для страниц конструкторов форм.
 */
abstract class AbstractAdminFormPage extends AbstractAdminPage
{
    protected readonly FieldMapper $fieldMapper;
    protected readonly AssetRegistryInterface $assetRegistry;

    public function __construct(
        protected readonly FormManager              $formManager,
        protected readonly FormConfigBuilder        $formBuilder,
        protected readonly TemplateManagerInterface $templateManager,
        protected readonly StringFilterInterface    $str,
        FieldMapper                                 $fieldMapper,
        AssetRegistryInterface                      $assetRegistry,
        AdminApiInterface                           $adminApi,
        HookManagerInterface                        $hookManager
    )
    {
        parent::__construct($adminApi, $hookManager);
        $this->fieldMapper = $fieldMapper;
        $this->assetRegistry = $assetRegistry;
    }

    /**
     * Регистрирует страницу как подменю.
     * @return string|null
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

        $this->assetRegistry->enqueueStyle(
            'usp-form-builder',
            USERSPACE_PLUGIN_URL . 'assets/css/form-builder.css',
            [],
            USERSPACE_VERSION
        );

        $this->assetRegistry->enqueueScript(
            'sortable-js',
            'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
            [],
            null,
            true
        );

        $this->assetRegistry->enqueueScript(
            'usp-form-builder-js',
            USERSPACE_PLUGIN_URL . 'assets/js/form-builder.js',
            ['usp-core', 'sortable-js'],
            USERSPACE_VERSION,
            true
        );

        $this->assetRegistry->enqueueStyle(
            'usp-form',
            USERSPACE_PLUGIN_URL . 'assets/css/form.css',
            [],
            USERSPACE_VERSION
        );

        $this->assetRegistry->localizeScript(
            'usp-form-builder-js',
            'uspApiSettings',
            [
                'root' => $this->str->escUrlRaw(rest_url()),
                'namespace' => USERSPACE_REST_NAMESPACE,
                'nonce' => wp_create_nonce('wp_rest'),
                'formType' => $this->getFormType(),
                'fieldTypes' => $this->fieldMapper->getMap(),
            ]
        );

        // Локализация для конструктора форм
        $this->assetRegistry->localizeScript(
            'usp-form-builder-js',
            'uspL10n',
            [
                'formBuilder' => [
                    'confirmDeleteSectionWithCustom' => $this->str->translate('This section contains custom fields. Deleting the section will permanently remove all data for these fields from all users. Are you sure?'),
                    'confirmDeleteBlockWithCustom' => $this->str->translate('This block contains custom fields. Deleting the block will permanently remove all data for these fields from all users. Are you sure?'),
                    'confirmMoveFields' => $this->str->translate('This section contains fields. They will be moved to "Available Fields". Are you sure?'),
                    'confirmDeleteCustomField' => $this->str->translate('This is a custom field. Deleting it will permanently remove all its data from all users. Are you sure?'),
                    'errorPrefix' => $this->str->translate('Error: '),
                    'fieldSettingsTitle' => $this->str->translate('Field Settings:'),
                    'fieldNameLabel' => $this->str->translate('Name (read-only)'),
                    'cancel' => $this->str->translate('Cancel'),
                    'save' => $this->str->translate('Save'),
                    'close' => $this->str->translate('Close'),
                    'createFieldTitle' => $this->str->translate('Create New Field'),
                    'newFieldNameLabel' => $this->str->translate('Name (unique identifier)'),
                    'newFieldTypeLabel' => $this->str->translate('Field Type'),
                    'selectType' => $this->str->translate('-- Select type --'),
                    'createFieldButton' => $this->str->translate('Create Field'),
                    'loading' => $this->str->translate('Loading...'),
                    'settingsLoadError' => $this->str->translate('Error loading settings'),
                    'nameAndTypeRequired' => $this->str->translate('Field name and type are required.'),
                    'kvValuePlaceholder' => $this->str->translate('Value'),
                    'kvLabelPlaceholder' => $this->str->translate('Label'),
                    'remove' => $this->str->translate('Remove'),
                    'saving' => $this->str->translate('Saving...'),
                    'unknownError' => $this->str->translate('Unknown error'),
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
        echo '<h1>' . $this->str->escHtml($this->getPageTitle()) . '</h1>';
        echo '<div id="usp-form-builder-notifications"></div>';
        echo $builderHtml;
        echo '<p class="submit">';
        echo '<button type="button" id="usp-save-form-builder" class="button button-primary">' . $this->str->translate('Save Changes') . '</button>';
        echo '</p>';

        // Подключаем скрытые HTML-шаблоны для JavaScript
        require_once $this->templateManager->getTemplatePath('admin_form_builder_templates');

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