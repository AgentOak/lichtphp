<?php
declare(strict_types=1);

namespace LichtPHP\Container\Plan;

use Closure;
use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\AutowiringException;
use LichtPHP\Container\ConstructionException;
use LichtPHP\Container\Container;
use LichtPHP\Container\ContainerConfigurationException;

class ClosurePlan extends Plan {
    /**
     * @param non-empty-list<class-string> $ids
     * @param array<string, mixed> $arguments
     * @param Closure(mixed...): object $closure
     * @throws ContainerConfigurationException
     */
    public function __construct(
        array $ids,
        array $arguments,
        protected readonly Closure $closure
    ) {
        parent::__construct($ids, $arguments);
        // TODO: Throw if Closure has a return type hint that is incompatible?
    }

    protected function produce(Container $container, array $arguments): object {
        try {
            $result = $container->get(Autowirer::class)->call($this->closure, $arguments);
        } catch (AutowiringException $e) {
            throw new ConstructionException("Failed to call Closure for IDs '{$this->asUnionType()}", previous: $e);
        }

        if (!is_object($result)) {
            throw new ConstructionException("Closure for IDs '{$this->asUnionType()}' produced a non-object");
        }

        foreach ($this->ids as $id) {
            if (!($result instanceof $id)) {
                $resultClass = $result::class;
                throw new ConstructionException("Closure for IDs '{$this->asUnionType()}' produced object of type "
                    . "'{$resultClass}' that is not an instance of specified ID '$id'");
            }
        }

        // TODO: Verify a different object has been created?

        return $result;
    }
}
