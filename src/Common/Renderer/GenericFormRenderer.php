<?php

namespace UserSpace\Common\Renderer;

use UserSpace\Common\Module\Form\Src\Infrastructure\FormFactory;
use UserSpace\Common\Module\Form\Src\Infrastructure\FormManager;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\String\StringFilterInterface;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

class GenericFormRenderer
{

    public function __construct(
        private readonly FormManager            $formManager,
        private readonly FormFactory            $formFactory,
        private readonly StringFilterInterface  $str,
        private readonly AssetRegistryInterface $assetRegistry
    )
    {
    }

    public function render(array $atts): string
    {
        $form_type = sanitize_key($atts['type'] ?? '');

        if (empty($form_type)) {
            return '<p style="color: red;">' . $this->str->translate('Error: "type" attribute not specified in the shortcode.') . '</p>';
        }

        $config = $this->formManager->load($form_type);

        if (null === $config) {
            return sprintf('<p style="color: red;">' . $this->str->translate('Error: form with type "%s" not found.') . '</p>', $this->str->escHtml($form_type));
        }

        $this->assetRegistry->enqueueStyle('usp-form');
        $this->assetRegistry->enqueueScript('usp-form-handler');
        $this->assetRegistry->localizeScript(
            'usp-form-handler',
            'uspL10n',
            [
                'formHandler' => [
                    'saving' => $this->str->translate('Saving...'),
                ],
            ]
        );

        // Если форма была отправлена (например, после неудачной валидации на стороне сервера),
        // заполняем DTO данными из $_POST, не пересобирая его.
        if (!empty($_POST)) {
            $fields = $config->getFields();
            foreach (array_keys($fields) as $fieldName) {
                if (isset($_POST[$fieldName])) {
                    $config->updateFieldValue($fieldName, $this->str->unslash($_POST[$fieldName]));
                }
            }
        }

        $form = $this->formFactory->create($config);

        ob_start();
        echo '<form method="post" class="usp-form" data-usp-form data-usp-action="' . $this->str->escAttr($atts['action'] ?? '') . '">';
        echo $form->render();
        echo '<div class="usp-form-submit-wrapper"><button type="submit">' . $this->str->translate('Save') . '</button></div>';
        echo '</form>';

        return ob_get_clean();
    }
}