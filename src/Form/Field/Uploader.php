<?php

namespace UserSpace\Form\Field;

use UserSpace\Core\SecurityHelper;
use UserSpace\Core\Form\Field\AbstractField;
use UserSpace\Form\Field\DTO\UploaderFieldDto;
use UserSpace\Form\Validator\AllowedTypesValidator;
use UserSpace\Form\Validator\ImageDimensionsValidator;
use UserSpace\Form\Validator\MaxFileSizeValidator;

class Uploader extends AbstractField
{
    private bool $multiple = false;
    private SecurityHelper $securityHelper;

    /**
     * @param UploaderFieldDto $dto
     */
    public function __construct( UploaderFieldDto $dto ) {
        parent::__construct( $dto );
        $this->multiple       = $dto->multiple;
        // В реальном приложении это будет внедряться через DI контейнер
        $this->securityHelper = new SecurityHelper();
    }

    public function renderInput(): string {
        $attachmentIds = array_filter( (array) $this->value );
        $previewHtml   = '';
        $hasFileClass  = ! empty( $attachmentIds ) ? 'has-file' : '';
        $isMultiple    = $this->multiple;

        foreach ( $attachmentIds as $attachmentId ) {
            $thumbnail_data = wp_get_attachment_image_src( (int) $attachmentId, 'thumbnail' );

            if ( $thumbnail_data ) {
                $previewUrl = $thumbnail_data[0];
            } else {
                // Если миниатюра не найдена, пытаемся получить иконку типа файла
                $previewUrl = wp_mime_type_icon( $attachmentId );
            }
            $previewHtml .= sprintf(
                '<div class="usp-uploader-preview-item" data-id="%d">
                    <img src="%s" alt="">
                    <button type="button" class="usp-remove-item-button">&times;</button>
                </div>',
                (int) $attachmentId,
                esc_url( $previewUrl )
            );
        }

        // Собираем data-атрибуты для валидации на клиенте из валидаторов
        $allowedTypes = '';
        $maxSize = '';
        $minWidth = '';
        $minHeight = '';
        $maxWidth = '';
        $maxHeight = '';

        foreach ($this->rules as $rule) {
            if ($rule instanceof AllowedTypesValidator) {
                $allowedTypes = implode(',', $rule->getAllowedTypes());
            } elseif ($rule instanceof MaxFileSizeValidator) {
                $maxSize = $rule->getMaxSizeMb();
            } elseif ($rule instanceof ImageDimensionsValidator) {
                $minWidth = $rule->getMinWidth() ?? '';
                $minHeight = $rule->getMinHeight() ?? '';
                $maxWidth = $rule->getMaxWidth() ?? '';
                $maxHeight = $rule->getMaxHeight() ?? '';
            }
        }

        $config = [
            'name'         => $this->name,
            'multiple'     => $this->multiple,
            'allowedTypes' => $allowedTypes,
            'maxSize'      => $maxSize,
            'minWidth'     => $minWidth,
            'minHeight'    => $minHeight,
            'maxWidth'     => $maxWidth,
            'maxHeight'    => $maxHeight,
        ];

        $validation_attrs = [
            'data-config'        => esc_attr( wp_json_encode( $config ) ),
            'data-signature'     => esc_attr( $this->securityHelper->sign( $config ) ),
            'data-max-size'      => esc_attr( $maxSize ),
            'data-min-width'     => esc_attr( $minWidth ),
            'data-min-height'    => esc_attr( $minHeight ),
            'data-max-width'     => esc_attr( $maxWidth ),
            'data-max-height'    => esc_attr( $maxHeight ),
            'data-multiple'      => $isMultiple ? 'true' : 'false',
        ];

        $removeButtonHtml = '';
        if ( ! empty( $attachmentIds ) ) {
            $removeButtonHtml = sprintf(
                '<button type="button" class="button button-link-delete usp-remove-button">%s</button>',
                esc_html__( 'Remove', 'usp' )
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
                <input type="file" class="usp-uploader-input" style="display: none;" %s>
                <input type="hidden" name="%s" value="%s" class="usp-uploader-value">
            </div>',
            esc_attr( $hasFileClass ),
            esc_attr( $this->name ),
            implode( ' ', array_map( fn( $k, $v ) => "$k=$v", array_keys( $validation_attrs ), $validation_attrs ) ),
            $previewHtml,
            esc_html__( 'Select File', 'usp' ),
            $removeButtonHtml,
            $isMultiple ? 'multiple' : '',
            esc_attr( $this->name . ( $isMultiple ? '[]' : '' ) ),
            esc_attr( implode( ',', $attachmentIds ) )
        );

        return $output;
    }

    public static function getSettingsFormConfig(): array {
        return array_merge(
            parent::getSettingsFormConfig(),
            [
                'multiple'         => [
                    'type'  => 'boolean',
                    'label' => __( 'Allow multiple file upload', 'usp' ),
                ],
                'allowed_types'    => [
                    'type'        => 'text',
                    'label'       => __( 'Allowed file types', 'usp' ),
                    'description' => __( 'Comma-separated MIME types, e.g., image/jpeg,image/png,application/pdf', 'usp' ),
                ],
                'max_size'         => [
                    'type'        => 'number',
                    'label'       => __( 'Max file size (MB)', 'usp' ),
                    'description' => __( 'Leave empty for no limit.', 'usp' ),
                ],
                'image_min_width'  => [
                    'type'  => 'number',
                    'label' => __( 'Min image width (px)', 'usp' ),
                ],
                'image_min_height' => [
                    'type'  => 'number',
                    'label' => __( 'Min image height (px)', 'usp' ),
                ],
                'image_max_width'  => [
                    'type'  => 'number',
                    'label' => __( 'Max image width (px)', 'usp' ),
                ],
                'image_max_height' => [
                    'type'  => 'number',
                    'label' => __( 'Max image height (px)', 'usp' ),
                ],
            ]
        );
    }
}