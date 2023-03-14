<?php
declare(strict_types=1);

namespace LichtPHP\Container\Plan;

use LichtPHP\Container\ContainerException;
use LichtPHP\Util;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

abstract class Plan {
    private bool $running = false;

    /**
     * @param non-empty-list<class-string> $ids
     * @param array<string, mixed> $arguments
     * @throws ContainerExceptionInterface
     */
    public function __construct(
        public readonly array $ids,
        private readonly array $arguments = []
    ) {
        if (count($ids) === 0) {
            throw new ContainerException("IDs may not be empty");
        }

        // TODO: Should this check be postponed to avoid triggering autoloader needlessly?
        foreach ($ids as $id) {
            if (!Util::isClassType($id)) {
                throw new ContainerException("ID '$id' is not a valid class type");
            }
        }
    }

    final public function asUnionType(): string {
        return implode("&", $this->ids);
    }

    /**
     * @param array<string, mixed> $arguments
     * @throws ContainerExceptionInterface
     */
    final public function execute(ContainerInterface $container, array $arguments = []): object {
        if ($this->running === true) {
            throw new ContainerException("Cyclic dependency detected, IDs '{$this->asUnionType()}' requires itself");
        }

        $this->running = true;
        try {
            return $this->produce($container, array_merge($this->arguments, $arguments));
        } finally {
            $this->running = false;
        }
    }

    /**
     * @param array<string, mixed> $arguments
     * @throws ContainerExceptionInterface
     */
    abstract protected function produce(ContainerInterface $container, array $arguments): object;
}