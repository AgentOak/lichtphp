<?php
declare(strict_types=1);

namespace LichtPHP\Container\Plan;

use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\AutowiringException;
use LichtPHP\Container\ConstructionException;
use LichtPHP\Container\Container;
use LichtPHP\Container\ContainerConfigurationException;
use LichtPHP\Util;

class ClassPlan extends Plan {
    /**
     * @param non-empty-list<class-string> $ids
     * @param array<string, mixed> $arguments
     * @param class-string $className
     * @throws ContainerConfigurationException
     */
    public function __construct(
        array $ids,
        array $arguments,
        protected readonly string $className
    ) {
        parent::__construct($ids, $arguments);

        // TODO: Postpone these checks to avoid triggering autoloader needlessly?
        if (!Util::isInstantiableClass($className)) {
            throw new ContainerConfigurationException("Class '$className' is not instantiable");
        }

        foreach ($ids as $id) {
            if (!is_a($className, $id, true)) {
                throw new ContainerConfigurationException("Class '$className' is not a subtype of specified ID '$id'");
            }
        }
    }

    protected function produce(Container $container, array $arguments): object {
        try {
            return $container->get(Autowirer::class)->instantiate($this->className, $arguments);
        } catch (AutowiringException $e) {
            throw new ConstructionException(
                "Failed to instantiate '{$this->className}' for IDs {$this->asUnionType()}",
                previous: $e
            );
        }
    }
}
