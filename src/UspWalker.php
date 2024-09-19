<?php

class UspWalker {

	public array $items = array();

	public function __construct( array $items ) {
		$this->items = $items;
	}

	public function get_item( string $name, mixed $value ): mixed {

		if ( ! $this->items ) {
			return false;
		}

		foreach ( $this->items as $item ) {

			if ( isset( $item->$name ) && $item->$name == $value ) {
				return $item;
			}
		}

		return false;
	}

	public function get_item_value( string $byname, string $nameValue, string $getName ): mixed {

		if ( ! $this->items ) {
			return false;
		}

		if ( ! $item = $this->get_item( $byname, $nameValue ) ) {
			return false;
		}

		return $item->$getName ?? false;
	}

	public function get_items( array $args = [] ): array {

		if ( ! $this->items ) {
			return [];
		}

		if ( ! $args ) {
			return $this->items;
		}

		$items = array();
		foreach ( $this->items as $item ) {

			$correct = true;
			foreach ( $args as $key => $value ) {
				if ( is_array( $value ) ) {
					if ( ! in_array( $item->$key, $value ) ) {
						$correct = false;
						break;
					}
				} else {
					if ( $item->$key != $value ) {
						$correct = false;
						break;
					}
				}
			}

			if ( $correct ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	public function get_field_values( string $field_name ): array {

		if ( ! $this->items ) {
			return [];
		}

		$fields = array();
		foreach ( $this->items as $item ) {
			if ( ! isset( $item->$field_name ) ) {
				continue;
			}
			$fields[] = $item->$field_name;
		}

		return $fields;
	}

	public function get_index_values( string $index_field, string $value_field ): array {

		if ( ! $this->items ) {
			return [];
		}

		$pack = array();
		foreach ( $this->items as $item ) {
			if ( ! isset( $item->$index_field ) || ! isset( $item->$value_field ) ) {
				continue;
			}
			$pack[ $item->$index_field ] = $item->$value_field;
		}

		return $pack;
	}

	public function is_set( $name, $value ): bool {

		if ( ! $this->items ) {
			return false;
		}

		foreach ( $this->items as $item ) {

			if ( isset( $item->$name ) && $item->$name == $value ) {
				return true;
			}
		}

		return false;
	}

	public function count( array $args = [] ): int {
		return count( $this->get_items( $args ) );
	}

}
