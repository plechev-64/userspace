<?php

namespace UserSpace\Form;

// Защита от прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Представляет собой горизонтальную секцию формы.
 */
class Section {

	/**
	 * @param string  $title  Заголовок секции.
	 * @param Block[] $blocks Массив блоков в секции.
	 */
	public function __construct(
		private readonly string $title,
		private readonly array $blocks
	) {
	}

	/**
	 * @return Block[]
	 */
	public function getBlocks(): array {
		return $this->blocks;
	}

    public function render( bool $isAdminContext = false ): string {
        if ( $isAdminContext ) {
            $output = '';
            if ( ! empty( $this->title ) ) {
                $output .= '<h2>' . esc_html( $this->title ) . '</h2>';
            }
            foreach ( $this->blocks as $block ) {
                $output .= $block->render( $isAdminContext );
            }
            return $output;
        }

        // Стандартный рендеринг для фронтенда
        $output = '<div class="usp-form-section">';
        if ( ! empty( $this->title ) ) {
            $output .= '<h3 class="usp-form-section-title">' . esc_html( $this->title ) . '</h3>';
        }
        $output .= '<div class="usp-form-section-blocks">';
        foreach ( $this->blocks as $block ) {
            $output .= $block->render( $isAdminContext );
        }
        $output .= '</div></div>';

        return $output;
    }
}