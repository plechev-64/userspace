<?php

namespace USP\Core\Module\Fields\Type;

use USP\Core\Module\Fields\FieldAbstract;

class FieldCustom extends FieldAbstract {

	public string $content;

	public function get_input(): ?string {
		return $this->content ?: null;
	}

}
