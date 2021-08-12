<?php

declare(strict_types = 1);

namespace Plattry\Ioc;

use Plattry\Ioc\Build\Maker;
use Plattry\Ioc\Exception\ContainerException;
use Plattry\Ioc\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package Plattry\Ioc
 */
class Container implements ContainerInterface
{
    /**
     * Global container instance
     * @var Container
     */
    protected static Container $global;

    /**
     * Shared resources
     * @var array
     */
    protected static array $bundle;

    /**
     * Object pool
     * @var object[]
     */
    protected array $pool = [];

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->pool[ContainerInterface::class] = $this;
    }

    /**
     * Get the container instance.
     * @return Container
     */
    public static function getGlobal(): Container
    {
        !isset(static::$global) &&
        (static::$global = new static());

        return static::$global;
    }

    /**
     * Set the global container instance.
     * @param Container $container
     * @return void
     */
    public static function setGlobal(Container $container): void
    {
        static::$global = $container;
    }

    /**
     * Get the bundle resources.
     * @param string|null $name
     * @return string|array|object
     * @throws ContainerException
     */
    public static function getBundle(string $name = null): string|array|object
    {
        if (is_null($name))
            return static::$bundle;

        if (isset(static::$bundle[$name]))
            return static::$bundle[$name];

        throw new ContainerException("Fail to get bundle, `$name` is not bound yet.");
    }

    /**
     * Set the bundle resources.
     * @param string|array $bundleOrName
     * @param string|object|null $resource
     * @throws ContainerException
     */
    public static function setBundle(string|array $bundleOrName, string|object $resource = null): void
    {
        if (is_array($bundleOrName)) {
            static::$bundle = $bundleOrName;
            return;
        }

        if (is_object($resource) || class_exists($resource)) {
            static::$bundle[$bundleOrName] = $resource;
            return;
        }

        throw new ContainerException(
            "Fail to set bundle, `$resource` must be a class name, instance and closure"
        );
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->pool[$id]) || isset(static::$bundle[$id]);
    }

    /**
     * @inheritDoc
     */
    public function get(string $id): object
    {
        if (isset($this->pool[$id]))
            return $this->pool[$id];

        if (isset(static::$bundle[$id])) {
            return $this->pool[$id] = (new Maker($this))->make(static::$bundle[$id]);
        }

        throw new NotFoundException("Not found resource `$id` in container.");
    }

    /**
     * Set an object and its id.
     * @param string $id
     * @param object $object
     * @return void
     */
    public function set(string $id, object $object): void
    {
        $this->pool[$id] = $object;
    }

    /**
     * Clear all objects in pool.
     * @return void
     */
    public function clear(): void
    {
        $this->pool = [];
    }
}
