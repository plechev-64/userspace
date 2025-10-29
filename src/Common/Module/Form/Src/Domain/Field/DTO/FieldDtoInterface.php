<?php

namespace UserSpace\Common\Module\Form\Src\Domain\Field\DTO;

if (!defined('ABSPATH')) {
    exit;
}

interface FieldDtoInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}