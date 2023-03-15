<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Closure;
use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Container\Plan\ClassPlan;
use LichtPHP\Container\Plan\ClosurePlan;
use LichtPHP\Container\Plan\Plan;
use LichtPHP\Util;
use Psr\Container\ContainerInterface;

/**
 * Implementation of PSR-11: Container Interface and `Container` that autowires entries.
 *
 * @see ContainerInterface
 * @see Container
 */
class AutowiringContainer extends StaticContainer implements Container {
    /**
     * @var array<class-string, Plan>
     */
    protected array $plans = [];

    /**
     * @param bool $implicitRegistration Whether instantiable classes should be registered automatically when they are
     *                                   attempted to be obtained from `make()` or `get()`. This is equivalent to
     *                                   calling `link($id, $id, [])` right before calling `make($id)` or `get($id)`.
     *                                   Explicit registrations (or `set($id, ...)`) can be made beforehand, but once
     *                                   a class was registered implicitly, it cannot be overwritten anymore.
     * @param bool $autowireMembers Whether to autowire methods and properties of created objects, see
     *                              `isAutowiringMembers()`
     * @throws ContainerConfigurationException
     * @see Container::isAutowiringMembers()
     */
    public function __construct(
        protected readonly bool $implicitRegistration,
        protected readonly bool $autowireMembers
    ) {
        parent::__construct();
        $this->set(Container::class, $this);
        $this->set(self::class, $this);

        $this->set(Autowirer::class, new Autowirer($this));
    }

    public function isAutowiringMembers(): bool {
        return $this->autowireMembers;
    }

    public function get(string $id): object {
        if (parent::has($id)) {
            return parent::get($id);
        }

        $implementation = $this->make($id);
        foreach ($this->plans[$id]->ids as $implementedId) {
            $this->set($implementedId, $implementation);
        }
        return $implementation;
    }

    public function has(string $id): bool {
        return parent::has($id) || $this->registered($id);
    }

    public function registered(string $id): bool {
        return array_key_exists($id, $this->plans) || $this->registeredImplicitly($id);
    }

    /**
     * @phpstan-assert-if-true class-string $id
     */
    protected function registeredImplicitly(string $id): bool {
        return $this->implicitRegistration && Util::isInstantiableClass($id);
    }

    public function make(string $id, array $arguments = []): object {
        if (!array_key_exists($id, $this->plans)) {
            if ($this->registeredImplicitly($id)) {
                $this->plans[$id] = new ClassPlan([ $id ], [], $id);
            } else {
                throw new NotFoundException("No registration for ID '$id'");
            }
        }

        return $this->plans[$id]->execute($this, $arguments);
    }

    /**
     * @throws ContainerConfigurationException
     */
    protected function register(Plan $plan): void {
        // Check all IDs first so we do not leave the container in a semi-broken state
        foreach ($plan->ids as $id) {
            if (array_key_exists($id, $this->plans)) {
                throw new ContainerConfigurationException("Duplicate registration for '$id' not supported");
            }
        }

        foreach ($plan->ids as $id) {
            $this->plans[$id] = $plan;
        }
    }

    public function link(string|array $ids, string $className, array $arguments = []): void {
        $this->register(new ClassPlan(is_array($ids) ? $ids : [ $ids ], $arguments, $className));
    }

    public function factory(string|array $ids, callable $factory, array $arguments = []): void {
        $this->register(new ClosurePlan(
            is_array($ids) ? $ids : [ $ids ],
            $arguments,
            $factory instanceof Closure ? $factory : $factory(...)
        ));
    }
}
