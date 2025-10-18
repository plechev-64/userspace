<?php

namespace UserSpace\Core\Rest\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapEntity
{
    public function __construct()
    {
    }
}
