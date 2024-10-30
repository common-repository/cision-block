<?php

declare(strict_types=1);

namespace CisionBlock\DI;

use CisionBlock\Psr\Container\ContainerExceptionInterface;

/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
