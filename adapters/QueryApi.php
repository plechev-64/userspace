<?php

namespace Adapters;

use UserSpace\Core\Query\QueryApiInterface;

if (!defined('ABSPATH')) {
    exit;
}

class QueryApi implements QueryApiInterface
{
    public function getQueryVar(string $varName, mixed $default = ''): mixed
    {
        return get_query_var($varName, $default);
    }
}