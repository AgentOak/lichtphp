<?php
declare(strict_types=1);

namespace LichtPHP\Container\Plan;

use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\AutowiringException;
use LichtPHP\Container\ConstructionException;
use LichtPHP\Container\Container;
use LichtPHP\Container\ContainerConfigurationException;
use LichtPHP\Container\CyclicDependencyException;
use LichtPHP\Util;
use Psr\Container\ContainerExceptionInterface;

abstract class Plan {
    private bool $running = false;

    /**
     * @param non-empty-list<class-string> $ids
     * @param array<string, mixed> $arguments
     * @throws ContainerConfigurationException
     */
    public function __construct(
        public readonly array $ids,
        private readonly array $arguments = []
    ) {
        if (count($ids) === 0) {
            throw new ContainerConfigurationException("IDs may not be empty");
        }

        // TODO: Should this check be postponed to avoid triggering autoloader needlessly?
        foreach ($ids as $id) {
            if (!Util::isClassType($id)) {
                throw new ContainerConfigurationException("ID '$id' is not a valid class type");
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
    final public function execute(Container $container, array $arguments = []): object {
        if ($this->running === true) {
            throw new CyclicDependencyException(
                "Cyclic dependency detected, IDs '{$this->asUnionType()}' requires itself"
            );
        }

        $this->running = true;
        try {
            $object = $this->produce($container, array_merge($this->arguments, $arguments));
            if ($container->isAutowiringMembers()) {
                $container->get(Autowirer::class)->autowire($object);
            }
            return $object;
        } catch (AutowiringException $e) {
            throw new ConstructionException("Failed autowiring object for IDs {$this->asUnionType()}", previous: $e);
        } finally {
            $this->running = false;
        }
    }

    /**
     * @param array<string, mixed> $arguments
     * @throws ContainerExceptionInterface
     */
    abstract protected function produce(Container $container, array $arguments): object;
}
