<?php

namespace Adapters;

use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\TransientApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Сервис для управления опциями WordPress.
 */
class OptionManager implements OptionManagerInterface
{
    public function __construct(private readonly TransientApiInterface $transientApi)
    {
    }

    /**
     * @inheritDoc
     */
    public function transient(): TransientApiInterface
    {
        return $this->transientApi;
    }

    /**
     * @inheritDoc
     */
    public function get(string $option, mixed $default = false): mixed
    {
        return get_option($option, $default);
    }

    /**
     * @inheritDoc
     */
    public function add(string $option, mixed $value, bool $autoload = true): bool
    {
        return add_option($option, $value, '', $autoload ? 'yes' : 'no');
    }

    /**
     * @inheritDoc
     */
    public function update(string $option, mixed $value): bool
    {
        return update_option($option, $value);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $option): bool
    {
        return delete_option($option);
    }

    /**
     * @inheritDoc
     */
    public function register(string $option_group, string $option_name, array $args = []): void
    {
        register_setting($option_group, $option_name, $args);
    }
}