<?php

class FieldCustom extends FieldAbstract {

	public string $content;

	public function __construct( array $args ) {
		parent::__construct( $args );
	}

	public function get_input(): ?string {
		return $this->content ?: null;
	}

}
