<?php

declare(strict_types = 1);

namespace Plattry\Ioc;

use Psr\Container\ContainerInterface;

/**
 * Trait ContainerAwareTrait
 * @package Plattry\Ioc
 */
trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container = null;

    /**
     * Sets a container.
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
