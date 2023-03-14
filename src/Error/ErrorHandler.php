<?php
declare(strict_types=1);

namespace LichtPHP\Error;

use Throwable;

interface ErrorHandler {
    // TODO: Translate $errno back to human-readable string
    // TODO: Documentation on ErrorHandlers, ErrorHandling
    public function onWarning(int $errno, string $errstr, string $errfile, int $errline): void;

    public function onUserException(UserException $userException): never;

    public function onUnexpectedException(Throwable $throwable): never;
}
