<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Form\Config;

// Защита от прямого доступа к файлу

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DTO для конфигурации секции формы.
 */
class SectionConfig
{
    /**
     * @var BlockConfig[]
     */
    private array $blocks = [];

    public function __construct(
        private readonly string $title
    )
    {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return BlockConfig[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function addBlock(BlockConfig $block): void
    {
        $this->blocks[] = $block;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'blocks' => array_map(fn(BlockConfig $block) => $block->toArray(), $this->blocks),
        ];
    }
}