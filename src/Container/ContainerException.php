<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Thrown by ArrayContainer and Container implementations in case of failures, the latter also wrapping
 * AutowiringExceptions when instantiating classes and calling callables.
 *
 * @see ArrayContainer
 * @see Container
 * @see AutowiringException
 */
class ContainerException extends Exception implements ContainerExceptionInterface {
}
