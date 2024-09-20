<?php

class OptionsManager {

	public array $boxes = [];
	public bool $extends = false;
	public bool $extend_options = false;
	public string $nonce = 'update-options';
	public ?string $page_options = null;
	public string $onclick = 'usp_update_options();return false;';
	public string $action = 'options.php';
	public string $method = 'post';
	public ?string $option_name = null;

	public function __construct( array $args = [] ) {

		if ( $args ) {
			$this->init_properties( $args );
		}

		if ( $this->extends ) {
			$this->extend_options = isset( $_COOKIE['usp_extends'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['usp_extends'] ) ) : 0;
		}
	}

	private function init_properties( array $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) & ! empty( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	public function isset_box( string $box_id ): bool {
		return isset( $this->boxes[ $box_id ] );
	}

	public function add_box( string $box_id, array $args ): OptionBox {
		$this->boxes[ $box_id ] = new OptionBox( $box_id, $args, $this->option_name );

		return $this->box( $box_id );
	}

	public function box( string $box_id ): OptionBox {
		return $this->boxes[ $box_id ];
	}

	public function get_menu(): ?string {

		if ( ! $this->boxes ) {
			return null;
		}

		$items = [];

		foreach ( $this->boxes as $box ) {

			$items[] = usp_get_button( [
				'data'    => [
					'options' => $box->box_id
				],
				'label'   => $box->title,
				'href'    => admin_url( 'admin.php?page=' . $this->page_options . '&usp-options-box=' . $box->box_id ),
				'onclick' => 'usp_onclick_options_label(this);return false;',
				'icon'    => $box->icon,
				'type'    => 'simple',
				'class'   => [ 'usp-options-bttn' ],
				'status'  => $box->active ? 'active' : ''
			] );
		}

		return '<div class="usp-options-tabs usp-wrap__widget">' . implode( '', $items ) . '</div>';

	}

	public function get_content(): string {

		if ( ! isset( $_GET['usp-options-box'] ) ) {
			foreach ( $this->boxes as $id => $box ) {
				$this->boxes[ $id ]->active = true;
				break;
			}
		}

		$content = '<form method="post" id="usp-options" class="usp-options' . ( $this->extend_options ? '' : ' usp-hide-more' ) . '" action="' . $this->action . '">';

		$content .= '<input type="hidden" name="page_options" value="' . $this->page_options . '">';

		$content .= wp_nonce_field( $this->nonce, '_wpnonce', true, false );

		$content .= '<div class="usp-option-menu usp-wrap__widget">';

		if ( $this->extends ) {
			$content .= usp_get_button( [
					'label'   => __( 'Advanced settings', 'userspace' ),
					'onclick' => 'return usp_enable_extend_options(this);',
					'icon'    => 'fa-square',
					'type'    => 'simple',
					'class'   => [ 'usp-toggle-extend', $this->extend_options ? 'usp-toggle-extend-show' : '' ],
				]
			);
		}

		foreach ( $this->boxes as $box ) {

			if ( ! $box->active ) {
				continue;
			}

			$content .= usp_get_button( [
					'label'      => $box->title,
					'onclick'    => 'usp_show_options_menu(this);return false;',
					'icon'       => 'fa-angle-down',
					'icon_align' => 'right',
					'type'       => 'clear',
					'style'      => 'text-align: center;',
					'fullwidth'  => true,
					'class'      => [ 'active-menu-item button button-primary button-large' ]
				]
			);
		}

		$content .= $this->get_menu();

		$content .= usp_get_button( [
			'label'     => __( 'Save settings', 'userspace' ),
			'onclick'   => $this->onclick ?: false,
			'submit'    => ! $this->onclick,
			'icon'      => 'fa-save',
			'type'      => 'clear',
			'size'      => 'medium',
			'fullwidth' => true,
			'class'     => [ 'usp-submit-options button button-primary' ]
		] );

		$content .= '</div>';

		$content .= '<div class="usp-option-fields">';

		foreach ( $this->boxes as $box ) {

			$content .= $box->get_content();
		}

		$content .= '</div>';

		$content .= '</form>';

		return $content;
	}

}
