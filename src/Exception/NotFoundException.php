<?php

declare(strict_types = 1);

namespace Plattry\Ioc\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 * @package Plattry\Ioc\Exception
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
