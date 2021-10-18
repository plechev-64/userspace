<?php

abstract class USP_Content_Manager {

	/**
	 * Request manager params
	 *
	 * @var array
	 */
	public $_request_params = [];

	/**
	 * Initial manager params
	 *
	 * @var array
	 */
	public $_init_params = [
		'pagenavi' => 0,
		'number'   => 30,
		'orderby'  => '',
		'order'    => 'DESC'
	];

	/**
	 * Custom default params ['key' => 'value']
	 *
	 * @var array
	 */
	public $_custom_default_params = [];

	/**
	 * Manager items retrieved from get_query
	 *
	 * @var array
	 */
	public $_items = [];

	/**
	 * Total manager items
	 *
	 * @var int
	 */
	public $_total_items = 0;

	/**
	 * @var USP_Pager
	 */
	public $_pager = null;

	/**
	 * USP_Query
	 *
	 * @var USP_Query
	 */
	public $_query = null;

	/**
	 * Initial manager params signature
	 *
	 * @var string
	 */
	public $_signature = '';

	/**
	 * @var WP_Error
	 */
	public $_error = null;

	public function __construct( array $init_params = [] ) {

		$this->_init_params = array_merge( $this->_init_params, $init_params );

		$this->_signature = $this->_create_signature( $this->get_init_params() );

		$this->_verify();

		if ( $this->has_error() ) {
			return;
		}

		$this->_init_request_params();

		$this->_query = $this->get_query();

		$this->set_total_items();

		if ( $this->get_param( 'pagenavi' ) ) {
			$this->init_pager();
		}

		if ( $this->_total_items ) {
			$this->set_data();
		}

		if ( $this->is_filters() ) {
			USP()->use_module( 'forms' );
		}

	}

	public function init_pager() {
		$this->_pager = new USP_Pager( [
			'number'    => $this->get_param( 'number' ),
			'total'     => $this->_total_items,
			'page_args' => [
				'onclick' => 'usp_load_content_manager_page("' . $this->get_id() . '", this);return false;',
				'href'    => '#'
			]
		] );
	}

	public function has_error() {
		return is_wp_error( $this->_error ) && $this->_error->has_errors();
	}

	public function get_param( $key, $default = null ) {

		return $this->_request_params[ $key ] ?? $this->_init_params[ $key ] ?? $this->_custom_default_params[ $key ] ?? $default;
	}

	public function get_error() {
		return $this->_error;
	}

	public function get_pager() {
		return $this->_pager;
	}

	public function get_classname() {
		return get_class( $this );
	}

	public function get_init_params() {
		return $this->_init_params;
	}

	public function get_items() {
		return $this->_items;
	}

	public function get_id() {
		return 'usp-content-manager-' . substr( $this->_signature, 0, 8 );
	}

	public function get_query() {
		return false;
	}

	public function get_search_fields() {
		return [];
	}

	public function get_buttons_args() {
		return [];
	}

	public function is_filters() {
		return $this->_pager || $this->get_search_fields();
	}

	public function set_custom_default_params( array $params ) {
		$this->_custom_default_params = array_merge( $this->_custom_default_params, $params );
	}

	public function set_data() {

		if ( ! $this->_query ) {
			return false;
		}

		$orderby = $this->get_param( 'orderby' );
		$order   = $this->get_param( 'order' );

		if ( $orderby && $order ) {
			$this->_query->orderby( $orderby, $order );
		}

		$offset = $this->_pager ? $this->_pager->offset : 0;

		$this->_items = $this->_query
			->limit( $this->get_param( 'number' ), $offset )
			->get_results();

		$this->_items = $this->filter_items( $this->_items );
	}

	public function filter_items( $items ) {
		return $items;
	}

	public function set_total_items() {

		if ( ! $this->_query ) {
			return 0;
		}

		$this->_total_items = $this->_query->get_count();

	}

	public function get_manager() {

		$id = $this->get_id();

		$result = '';

		if ( $this->is_filters() ) {
			$result = "<form id='$id' onsubmit='usp_content_manager_submit(\"$id\");return false;' method='post' class='preloader-parent usp-content-manager-form'>";
		}

		$result .= '<div class="usp-content-manager">' . $this->build_manager_content() . '</div>';

		if ( $this->is_filters() ) {
			$result .= '</form>';
		}

		return $result;
	}

	public function build_manager_content() {

		$content = '';

		if ( $this->is_filters() ) {
			$content .= $this->get_hidden_fields();
		}

		$content .= $this->get_buttons();

		$content .= $this->get_search();

		$content .= $this->get_content_body();

		return $content;

	}

	public function get_content_body() {

		$content = '<div class="usp-content-manager__body">';

		$content .= $this->get_page_navi();

		$content .= $this->get_items_content();

		$content .= $this->get_page_navi();

		$content .= '</div>';

		return $content;

	}

	public function get_page_navi() {
		if ( $this->get_param( 'pagenavi' ) && $this->get_pager()->pages > 1 ) {
			return $this->get_pager()->get_pager();
		}

		return '';
	}

	public function get_hidden_fields() {

		$content = '<input type="hidden" name="pagenum" value="' . $this->get_pager()->current . '">';
		$content .= '<input type="hidden" name="classname" value="' . $this->get_classname() . '">';
		$content .= '<input type="hidden" name="startstate" value=' . esc_js( json_encode( $this->get_init_params() ) ) . '>';
		$content .= '<input type="hidden" name="_s" value=' . $this->_signature . '>';

		return $content;
	}

	public function get_buttons() {

		$buttonsArgs = $this->get_buttons_args();

		if ( ! $buttonsArgs ) {
			return '';
		}

		$content = '<div class="usp-content-manager-buttons">';

		foreach ( $buttonsArgs as $args ) {
			$content .= usp_get_button( $args );
		}

		$content .= '</div>';

		return $content;
	}

	public function get_search() {

		$fields = $this->get_search_fields();

		if ( ! $fields ) {

			return '';
		}

		$form = new USP_Form( array(
				'fields'  => $fields,
				'submit'  => __( 'Search', 'userspace' ),
				'onclick' => 'usp_content_manager_submit("' . $this->get_id() . '");return false;'
			)
		);

		$search_form = '<div class="form-fields">';
		$search_form .= $form->get_fields_list();
		$search_form .= '</div>';
		$search_form .= $form->get_submit_box();

		$filter_classes = implode( ' ', [
			'usp-form',
			'usp-content-manager__filter'
		] );

		$search_form_wrapper = '<div class="' . $filter_classes . '">';
		$search_form_wrapper .= $search_form;
		$search_form_wrapper .= '</div>';

		return $search_form_wrapper;
	}

	public function get_items_content() {

		$items = $this->get_items();

		$content = '<div class="usp-content-manager-content">';

		if ( ! $items ) {

			$content .= $this->get_no_result_notice();

		} else {

			foreach ( $items as $item ) {
				$content .= $this->get_item_content( $item );
			}
		}

		$content .= '</div>';

		return $content;
	}

	public function get_no_result_notice() {
		return usp_get_notice( [ 'text' => __( 'Nothing found', 'userspace' ) ] );
	}

	public function get_item_content( $item ) {
		return '';
	}

	private function _set_request_param( $key, $value ) {

		//todo maybe sanitize
		$this->_request_params[ $key ] = $value;

	}

	private function _init_request_params() {

		$this->_init_search_form_params();

	}

	private function _init_search_form_params() {

		$search_fields = $this->get_search_fields();

		if ( ! $search_fields ) {
			return;
		}

		foreach ( $search_fields as $field ) {

			$field_slug = $field['slug'];

			if ( isset( $_REQUEST[ $field_slug ] ) ) {
				/**
				 * @var USP_Field_Abstract $_field
				 */
				$_field = USP_Field::setup( $field );

				if ( $_field->is_valid_value( $_REQUEST[ $field_slug ] ) ) {
					$this->_set_request_param( $field_slug, $_REQUEST[ $field_slug ] );
				}

			}


		}

	}

	private function _verify() {

		if ( $this->_is_request_process() && ! $this->_is_signature_valid() ) {

			$this->_error = new WP_Error( 'usp_content_manager', __( 'Signature invalid', 'userspace' ) );

		}
	}

	private function _is_request_process() {
		return usp_is_ajax() && ! empty( $_POST['_s'] );
	}

	private function _is_signature_valid() {
		return ! empty( $_POST['_s'] ) && $_POST['_s'] === $this->_signature;
	}

	private function _create_signature( $params ) {
		$salt = usp_get_option( 'usp_security_key' );
		ksort( $params );
		$params     = json_encode( $params );
		$class_name = $this->get_classname();

		return md5( $class_name . $params . $salt );
	}

}
