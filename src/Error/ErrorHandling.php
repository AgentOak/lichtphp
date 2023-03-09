<?php
declare(strict_types=1);

namespace LichtPHP\Error;

use ErrorException;
use LogicException;
use Throwable;

final class ErrorHandling {
    /**
     * @var int
     */
    private const NON_FATAL_ERRORS = E_STRICT | E_DEPRECATED | E_USER_DEPRECATED | E_USER_NOTICE | E_USER_WARNING;

    /**
     * @var array<int, ErrorHandling>
     */
    private static array $errorHandling = [];

    private function __construct(
        private readonly ErrorHandler $errorHandler
    ) {
    }

    public static function install(ErrorHandler $errorHandler): void {
        self::$errorHandling[] = new ErrorHandling($errorHandler);

        //ini_set("display_errors", "Off");
        //ini_set("display_startup_errors", "Off");
        //ini_set("log_errors", "On");
        //error_reporting(E_ALL);

        set_error_handler(end(self::$errorHandling)->handleError(...));
        set_exception_handler(end(self::$errorHandling)->handleException(...));
    }

    public static function uninstall(): void {
        if (array_pop(self::$errorHandling) === null) {
            throw new LogicException("No error handler has been installed");
        }

        restore_exception_handler();
        restore_error_handler();
    }

    /**
     * @throws ErrorException
     */
    private function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        // Detect error suppression; also allows operator to ignore errors through ini
        if ((error_reporting() & $errno) === 0) {
            return true;
        }

        // Log non-fatal errors
        if (($errno & self::NON_FATAL_ERRORS) !== 0) {
            $this->errorHandler->onWarning($errno, $errstr, $errfile, $errline);

            return true; // Do not ever run the weird php error handler
        }

        // Throw exception for unexpected, possibly critical error
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    private function handleException(Throwable $throwable): never {
        if ($throwable instanceof UserException) {
            $this->errorHandler->onUserException($throwable);
        }

        $this->errorHandler->onUnexpectedException($throwable);
    }
}
