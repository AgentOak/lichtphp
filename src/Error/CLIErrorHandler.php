<?php
declare(strict_types=1);

namespace LichtPHP\Error;

use Throwable;

class CLIErrorHandler implements ErrorHandler {
    /**
     * @var int<1, 254>
     */
    private const DEFAULT_EXIT_CODE = 1;

    public function onWarning(int $errno, string $errstr, string $errfile, int $errline): void {
        fwrite(STDERR, $errstr . PHP_EOL . "\t(in $errfile:$errline)" . PHP_EOL);
    }

    public function onUserException(UserException $userException): never {
        $this->terminate("Error: " . $userException->getMessage(), $userException->getExitCode());
    }

    public function onUnexpectedException(Throwable $throwable): never {
        $this->terminate("Terminating due to uncaught exception: $throwable");
    }

    /**
     * @param non-empty-string $message
     * @param int<1, 254> $exitCode
     */
    protected function terminate(string $message, int $exitCode = self::DEFAULT_EXIT_CODE): never {
        fwrite(STDERR, PHP_EOL . $message . PHP_EOL . PHP_EOL);
        exit($exitCode);
    }
}
