<?php

namespace UserSpace\Form;

use InvalidArgumentException;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Фабрика для создания объектов Form.
 */
class FormFactory {

	public function __construct( private readonly FieldMapper $fieldMapper ) {

	}

	/**
	 * Создает экземпляр формы на основе конфигурации.
	 *
	 * @param array $config Конфигурация полей формы.
	 *
	 * @return Form
	 * @throws InvalidArgumentException Если указан неподдерживаемый тип поля.
	 */
	public function create( array $config ): Form {
		$sections = [];
		$section_configs = $config['sections'] ?? [];

		foreach ( $section_configs as $section_config ) {
			$blocks = [];
			$block_configs = $section_config['blocks'] ?? [];

			foreach ( $block_configs as $block_config ) {
				$fields = [];
				$field_configs = $block_config['fields'] ?? [];

				foreach ( $field_configs as $name => $field_config ) {
					$type       = $field_config['type'] ?? 'text';
					$class_name = $this->fieldMapper->getClass( $type );
                    $dtoClass = $this->fieldMapper->getDtoClass( $type );

					// Делегируем создание DTO самому классу поля
					//$dto = $class_name::createDtoFromConfig( $name, $field_config );

					$fields[] = new $class_name( new $dtoClass($name, $field_config) );
				}
				$blocks[] = new Block( $block_config['title'] ?? '', $fields );
			}
			$sections[] = new Section( $section_config['title'] ?? '', $blocks );
		}

		return new Form( $sections );
	}
}