<?php

declare(strict_types = 1);

namespace Plattry\Ioc\Build;

use Closure;
use Plattry\Ioc\Container;
use Plattry\Ioc\Exception\ContainerException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionUnionType;

/**
 * Class Maker
 * @package Plattry\Ioc
 */
class Maker
{
    /**
     * Current container
     * @var Container
     */
    protected Container $container;

    /**
     * Maker constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Make an object.
     * @param string|object $resource
     * @param array $vars
     * @return object
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function make(string|object $resource, array $vars = []): object
    {
        if ($resource instanceof Closure) {
            return $this->invokeFunc($resource, $vars);
        }

        if (is_object($resource)) {
            return $resource;
        }

        return $this->invokeClass($resource, $vars);
    }

    /**
     * Invoke a closure by reflection.
     * @param Closure $name
     * @param array $vars
     * @return object
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function invokeFunc(Closure $name, array $vars): object
    {
        $refFunc = new ReflectionFunction($name);

        $args = $this->getArgs($refFunc, $vars);

        return $refFunc->invokeArgs($args);
    }

    /**
     * Invoke a class by reflection.
     * @param string $name
     * @param array $vars
     * @return object
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function invokeClass(string $name, array $vars): object
    {
        $refClass = new ReflectionClass($name);

        if (!$refClass->isInstantiable())
            throw new ContainerException("Fail to invoke class, `$name` is not instantiable.");

        $refConstruct = $refClass->getConstructor();
        if (is_null($refConstruct))
            return $refClass->newInstanceWithoutConstructor();

        $args = $this->getArgs($refConstruct, $vars);

        return $refClass->newInstanceArgs($args);
    }

    /**
     * Get arguments.
     * @param ReflectionFunctionAbstract $refFunc
     * @param array $vars
     * @return array
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function getArgs(ReflectionFunctionAbstract $refFunc, array $vars = []): array
    {
        return array_map(
            fn($refParam) => $this->parseParameter($refParam, $vars),
            $refFunc->getParameters()
        );
    }

    /**
     * Parse a parameter.
     * @param ReflectionParameter $refParam
     * @param array $vars
     * @return mixed
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function parseParameter(ReflectionParameter $refParam, array $vars = []): mixed
    {
        $name = $refParam->getName();
        if (isset($vars[$name]))
            return $vars[$name];

        if ($refParam->hasType()) {
            $refType = $refParam->getType();
            if ($refType instanceof ReflectionUnionType) {
                $refTypes = $refType->getTypes();
            } else {
                $refTypes = [$refType];
            }

            foreach ($refTypes as $refSubType) {
                $typeName = $refSubType->getName();
                if (!$this->container->has($typeName))
                    continue;

                return $this->container->get($typeName);
            }

            if ($refType->allowsNull())
                return null;
        }

        if ($refParam->isDefaultValueAvailable())
            return $refParam->getDefaultValue();

        throw new ContainerException("Fail to parse parameter, `$name` is missing.");
    }
}
