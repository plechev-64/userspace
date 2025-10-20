<?php

namespace Adapters;

use UserSpace\Core\TransientApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

class TransientApi implements TransientApiInterface
{
    public function get(string $transient): mixed
    {
        return get_transient($transient);
    }

    public function set(string $transient, mixed $value, int $expiration = 0): bool
    {
        return set_transient($transient, $value, $expiration);
    }

    public function delete(string $transient): bool
    {
        return delete_transient($transient);
    }
}