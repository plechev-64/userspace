<?php
/**
 * HTML шаблоны для конструктора форм.
 *
 * Эти шаблоны загружаются на странице конструктора и используются JavaScript
 * для динамического создания новых элементов (секций, блоков, полей).
 *
 * @package UserSpace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<template id="usp-template-section">
	<div class="usp-form-builder-section" data-id="__SECTION_ID__">
		<div class="usp-form-builder-section-header">
            <h3 class="usp-form-builder-section-title"><input type="text" class="title-input" value="__SECTION_TITLE__" placeholder="<?php esc_attr_e( 'Untitled Section', 'usp' ); ?>" /></h3>
            <div class="usp-form-builder-section-actions">
				<button type="button" class="button" data-action="add-block"><?php _e( 'Add Block', 'usp' ); ?></button>
				<button type="button" class="button usp-button-link-delete" data-action="delete-section"><?php _e( 'Delete', 'usp' ); ?></button>
			</div>
		</div>
		<div class="usp-form-builder-blocks" data-sortable="blocks"></div>
	</div>
</template>

<template id="usp-template-block">
	<div class="usp-form-builder-block" data-id="__BLOCK_ID__">
		<div class="usp-form-builder-block-header">
            <h4 class="usp-form-builder-block-title"><input type="text" class="title-input" value="__BLOCK_TITLE__" placeholder="<?php esc_attr_e( 'Untitled Block', 'usp' ); ?>" /></h4>
            <div class="usp-form-builder-block-actions">
				<button type="button" class="button usp-button-link-delete" data-action="delete-block"><?php _e( 'Delete', 'usp' ); ?></button>
			</div>
		</div>
		<div class="usp-form-builder-fields" data-sortable="fields"></div>
		<div class="usp-form-builder-block-footer"><button type="button" class="button button-secondary" data-action="add-custom-field"><?php _e( 'Add Field', 'usp' ); ?></button></div>
	</div>
</template>

<template id="usp-template-field">
	<div class="usp-form-builder-field" data-name="__FIELD_NAME__" data-type="__FIELD_TYPE__" data-config='__FIELD_CONFIG__' data-is-custom="true">
		<span class="field-label">__FIELD_LABEL__</span>
		<span class="field-type">[__FIELD_TYPE__]</span>
		<div class="usp-form-builder-field-actions">
			<button type="button" class="button button-small" data-action="edit-field"><?php _e( 'Edit', 'usp' ); ?></button>
			<button type="button" class="button button-small usp-button-link-delete" data-action="delete-field"><?php _e( 'Delete', 'usp' ); ?></button>
		</div>
	</div>
</template>