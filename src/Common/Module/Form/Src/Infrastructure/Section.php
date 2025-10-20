<?php

namespace UserSpace\Common\Module\Form\Src\Infrastructure;

use Adapters\StringFilter;

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Представляет собой горизонтальную секцию формы.
 */
class Section
{
    private StringFilter $str;

    /**
     * @param string $title Заголовок секции.
     * @param Block[] $blocks Массив блоков в секции.
     */
    public function __construct(
        private readonly string $title,
        private readonly array  $blocks
    )
    {
        $this->str = new StringFilter();
    }

    /**
     * @return Block[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function render(bool $isAdminContext = false): string
    {
        if ($isAdminContext) {
            $output = '';
            if (!empty($this->title)) {
                $output .= '<h2>' . $this->str->escHtml($this->title) . '</h2>';
            }
            foreach ($this->blocks as $block) {
                $output .= $block->render($isAdminContext);
            }
            return $output;
        }

        // Стандартный рендеринг для фронтенда
        $output = '<div class="usp-form-section">';
        if (!empty($this->title)) {
            $output .= '<h3 class="usp-form-section-title">' . $this->str->escHtml($this->title) . '</h3>';
        }
        $output .= '<div class="usp-form-section-blocks">';
        foreach ($this->blocks as $block) {
            $output .= $block->render($isAdminContext);
        }
        $output .= '</div></div>';

        return $output;
    }
}