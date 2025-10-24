<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure\Field;

use UserSpace\Adapters\StringFilter;
use UserSpace\Common\Module\Form\Src\Domain\Field\AbstractField;
use UserSpace\Common\Module\Form\Src\Infrastructure\Field\DTO\UploaderAbstractFieldDto;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\AllowedTypesValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\ImageDimensionsValidator;
use UserSpace\Common\Module\Form\Src\Infrastructure\Validator\MaxFileSizeValidator;
use UserSpace\Common\Module\Media\Src\Domain\MediaApiInterface;
use UserSpace\Core\SecurityHelperInterface;

class Uploader extends AbstractField
{
    private readonly bool $multiple;

    /**
     * @param UploaderAbstractFieldDto $dto
     * @param SecurityHelperInterface $securityHelper
     * @param MediaApiInterface $mediaApi
     */
    public function __construct(
        UploaderAbstractFieldDto                 $dto,
        private readonly SecurityHelperInterface $securityHelper,
        private readonly MediaApiInterface       $mediaApi
    )
    {
        parent::__construct($dto);
        $this->multiple = $dto->multiple;
    }

    public function renderInput(): string
    {
        $attachmentIds = array_filter((array)$this->value);
        $previewHtml = '';
        $hasFileClass = !empty($attachmentIds) ? 'has-file' : '';
        $isMultiple = $this->multiple;

        foreach ($attachmentIds as $attachmentId) {
            $previewUrl = $this->mediaApi->getAttachmentImageUrl((int)$attachmentId, 'thumbnail');

            if (!$previewUrl) {
                // Если миниатюра не найдена, пытаемся получить иконку типа файла
                $previewUrl = $this->mediaApi->getMimeTypeIconUrl((int)$attachmentId);
            }
            $previewHtml .= sprintf(
                '<div class="usp-uploader-preview-item" data-id="%d">
                    <img src="%s" alt="">
                    <button type="button" class="usp-remove-item-button">&times;</button>
                </div>',
                (int)$attachmentId,
                $this->str->escUrl($previewUrl)
            );
        }

        // Собираем data-атрибуты для валидации на клиенте из валидаторов
        $allowedTypes = null;
        $maxSize = null;
        $minWidth = null;
        $minHeight = null;
        $maxWidth = null;
        $maxHeight = null;

        foreach ($this->rules as $rule) {
            if ($rule instanceof AllowedTypesValidator) {
                $allowedTypes = implode(',', $rule->getAllowedTypes());
            } elseif ($rule instanceof MaxFileSizeValidator) {
                $maxSize = $rule->getMaxSizeMb();
            } elseif ($rule instanceof ImageDimensionsValidator) {
                $minWidth = $rule->getMinWidth();
                $minHeight = $rule->getMinHeight();
                $maxWidth = $rule->getMaxWidth();
                $maxHeight = $rule->getMaxHeight();
            }
        }

        $config = [
            'name' => $this->name,
            'multiple' => $this->multiple,
            'allowedTypes' => $allowedTypes,
            'maxSize' => $maxSize,
            'minWidth' => $minWidth,
            'minHeight' => $minHeight,
            'maxWidth' => $maxWidth,
            'maxHeight' => $maxHeight,
        ];
        $validation_attrs = [
            'data-config' => $this->str->escAttr($this->str->jsonEncode($config)) ?: '',
            'data-signature' => $this->str->escAttr($this->securityHelper->sign($config)) ?: '',
            'data-max-size' => $this->str->escAttr($maxSize) ?: '',
            'data-min-width' => $this->str->escAttr($minWidth) ?: '',
            'data-min-height' => $this->str->escAttr($minHeight) ?: '',
            'data-max-width' => $this->str->escAttr($maxWidth) ?: '',
            'data-max-height' => $this->str->escAttr($maxHeight) ?: '',
            'data-multiple' => $isMultiple ? 'true' : 'false',
            'data-field-name' => $this->str->escAttr($this->name) ?: '',
        ];

        $removeButtonHtml = '';
        if (!empty($attachmentIds)) {
            $removeButtonHtml = sprintf(
                '<button type="button" class="button button-link-delete usp-remove-button">%s</button>',
                $this->str->translate('Remove')
            );
        }

        // Генерируем скрытые поля для отправки данных
        $hiddenInputsHtml = '';
        if ($isMultiple) {
            // Для множественной загрузки создаем по одному скрытому полю на каждый ID
            foreach ($attachmentIds as $id) {
                $hiddenInputsHtml .= sprintf(
                    '<input type="hidden" name="%s[]" value="%s" class="usp-uploader-managed-value">',
                    $this->str->escAttr($this->name),
                    $this->str->escAttr($id)
                );
            }
        } else {
            // Для одиночной загрузки создаем одно поле
            $singleValue = !empty($attachmentIds) ? $attachmentIds[0] : '';
            $hiddenInputsHtml = sprintf(
                '<input type="hidden" name="%s" value="%s" class="usp-uploader-value">',
                $this->str->escAttr($this->name),
                $this->str->escAttr($singleValue)
            );
        }

        $output = sprintf(
            '<div class="usp-uploader %s" data-field-name="%s" %s>
                <div class="usp-uploader-preview-wrapper">%s</div>
                <div class="usp-uploader-actions">
                    <button type="button" class="button usp-upload-button">%s</button>
                    %s
                    <span class="usp-uploader-status"></span>
                </div>
                <input type="file" class="usp-uploader-input" style="display: none;" %s>%s
            </div>',
            $this->str->escAttr($hasFileClass),
            $this->str->escAttr($this->name),
            implode(' ', array_map(fn($k, $v) => "$k='$v'", array_keys($validation_attrs), $validation_attrs)),
            $previewHtml,
            $this->str->translate('Select File'),
            $removeButtonHtml,
            $isMultiple ? 'multiple' : '',
            $hiddenInputsHtml // Вставляем сгенерированные скрытые поля
        );

        return $output;
    }

    public static function getSettingsFormConfig(): array
    {
        return array_merge(
            parent::getSettingsFormConfig(),
            [
                'multiple' => [
                    'type' => 'boolean',
                    'label' => StringFilter::sTranslate('Allow multiple file upload'),
                ],
                'allowed_types' => [
                    'type' => 'text',
                    'label' => StringFilter::sTranslate('Allowed file types'),
                    'description' => StringFilter::sTranslate('Comma-separated MIME types, e.g., image/jpeg,image/png,application/pdf'),
                ],
                'max_size' => [
                    'type' => 'number',
                    'label' => StringFilter::sTranslate('Max file size (MB)'),
                    'description' => StringFilter::sTranslate('Leave empty for no limit.'),
                ],
                'image_min_width' => [
                    'type' => 'number',
                    'label' => StringFilter::sTranslate('Min image width (px)'),
                ],
                'image_min_height' => [
                    'type' => 'number',
                    'label' => StringFilter::sTranslate('Min image height (px)'),
                ],
                'image_max_width' => [
                    'type' => 'number',
                    'label' => StringFilter::sTranslate('Max image width (px)'),
                ],
                'image_max_height' => [
                    'type' => 'number',
                    'label' => StringFilter::sTranslate('Max image height (px)'),
                ],
            ]
        );
    }
}