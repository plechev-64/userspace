<?php

namespace UserSpace\Core\Addon;

if ( ! defined('ABSPATH')) {
    exit;
}

interface AddonManagerInterface
{
    /**
     * Регистрирует класс дополнения.
     *
     * @param string $addonClassName Полное имя класса дополнения.
     * @return void
     */
    public function register(string $addonClassName): void;

    public function initializeAddons(): void;
}