<?php

declare(strict_types = 1);

namespace Plattry\Ioc\Facade;

use Plattry\Ioc\Container;
use Plattry\Ioc\Exception\ContainerException;

/**
 * Class FacadeAbstract
 * @package Plattry\Ioc
 */
abstract class FacadeAbstract
{
    /**
     * Create the instance of actually invoking method.
     * @return object
     * @throws ContainerException
     */
    public static function run(): object
    {
        $name = static::getCallName();

        if (!Container::getGlobal()->has($name)) {
            Container::setBundle($name, static::getCallClass());
        }

        return Container::getGlobal()->get($name);
    }

    /**
     * Get the name of invoking method.
     * @return string
     */
    abstract public static function getCallName(): string;

    /**
     * Get the class full name of actually invoking method.
     * @return string|object
     */
    abstract public static function getCallClass(): string|object;

    /**
     * Call the normal method statically.
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws ContainerException
     */
    public static function __callStatic(string $method, array $args = []): mixed
    {
        return call_user_func_array([static::run(), $method], $args);
    }
}
