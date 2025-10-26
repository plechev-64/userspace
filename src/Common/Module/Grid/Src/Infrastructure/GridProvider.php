<?php

namespace UserSpace\Common\Module\Grid\Src\Infrastructure;

use UserSpace\Common\Module\Grid\Src\Domain\AbstractListContentGrid;
use UserSpace\Core\Container\ContainerInterface;
use UserSpace\Core\Exception\UspException;

/**
 * Предоставляет экземпляры гридов по их типу.
 */
class GridProvider
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array              $gridMap
    )
    {
    }

    /**
     * @throws UspException
     */
    public function getGrid(string $gridType): AbstractListContentGrid
    {
        if (!isset($this->gridMap[$gridType])) {
            throw new UspException("Grid type '{$gridType}' is not registered.", 404);
        }

        $gridClass = $this->gridMap[$gridType];

        return $this->container->get($gridClass);
    }
}