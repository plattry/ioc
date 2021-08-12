<?php

declare(strict_types = 1);

namespace Plattry\Ioc\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Class ContainerException
 * @package Plattry\Ioc\Exception
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
}
