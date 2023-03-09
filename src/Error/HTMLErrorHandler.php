<?php
declare(strict_types=1);

namespace LichtPHP\Error;

use Throwable;

class HTMLErrorHandler implements ErrorHandler {
    /**
     * @var int<400, 599>
     */
    private const DEFAULT_HTTP_CODE = 500;

    public function onWarning(int $errno, string $errstr, string $errfile, int $errline): void {
        error_log($errstr . PHP_EOL . "\t(in $errfile:$errline)");
    }

    public function onUserException(UserException $userException): never {
        $this->terminate($userException->getMessage(), $userException->getHttpCode());
    }

    public function onUnexpectedException(Throwable $throwable): never {
        error_log("Terminating due to uncaught exception: $throwable");
        $this->terminate("An unexpected error occured. Please contact administration if this problem persists.");
    }

    /**
     * @param string $message
     * @param int<400, 599> $httpCode
     */
    protected function terminate(string $message, int $httpCode = self::DEFAULT_HTTP_CODE): never {
        // phpcs:disable
        while (@ob_get_level()) {
            @ob_end_clean();
        }
        // phpcs:enable

        if (headers_sent()) {
            // TODO: Can this safely be trigger_error?
            if ((error_reporting() & E_USER_WARNING) !== 0) {
                $this->onWarning(
                    E_USER_WARNING,
                    "Headers already sent when handling error, user-facing output may be mangled.",
                    __FILE__,
                    __LINE__
                );
            }
            echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
        } else {
            // TODO: Set no-cache headers?
            http_response_code($httpCode);
            header("Content-Type: text/plain; charset=utf-8");
            echo $message;
        }

        exit();
    }
}
