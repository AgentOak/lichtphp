<?php
declare(strict_types=1);

namespace LichtPHP\Autowiring;

use Closure;
use DomainException;
use InvalidArgumentException;
use LichtPHP\Util;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;

/**
 * Provides methods for autowiring, obtaining dependency instances from a ContainerInterface. Autowiring is implemented
 * by reading parameter and property typehints from the PHP reflection API and using the type name as an ID to fetch
 * from the ContainerInterface.
 *
 * Callables (`call()`), constructors (`instantiate()`) and objects methods and properties with the Autowired attribute
 * (`autowire()`) are supported. Callables and methods (including the constructor) will be called with their parameters
 * autowired. By-reference and variadic parameters are not supported and will throw an `AutowiringException` whenever
 * they are encountered.
 *
 * An array of named arguments can be passed to the `call()` and `instantiate()` methods. The corresponding parameters
 * will be supplied preferably by the given arguments and bypass autowiring. This should only be used sparingly, e.g.
 * when necessary due to non-autowireable parameters or when obtaining different instances from `Container::make()`.
 *
 * Constructor and method parameters that are not typehinted with a class name, but have a default value, are ignored
 * and retain their default value. Otherwise, parameters MUST be typehinted with a class name, or an
 * `AutowiringException` will be thrown. Properties with the `Autowired` attribute MUST always be typehinted with a
 * class name, otherwise an `AutowiringException` will be thrown. Union and intersection types are not supported and
 * will throw an `AutowiringException` when they are about to be autowired, unless they are supplied from provided
 * arguments.
 *
 * Specifying a default value for a parameter or property makes an optional dependency; the Autowirer will try to
 * resolve the dependency but if it is not available, it will use the default value. Additionally, nullable types may
 * also be used to specify an optional dependency; if the Autowirer cannot resolve the dependency, it will assign
 * `null`. Default values have precedence over `null`. If neither the type is nullable, nor a default value is given,
 * the dependency is required and an `AutowiringException` will be thrown if it cannot be satisfied.
 *
 * @see ContainerInterface
 * @see Autowired
 */
class Autowirer {
    // TODO: Support variadics, supplying exactly one argument? Treat as optional?
    // TODO: Expand with different strategies to autowire variables? separate ContainerInterface for variable name?
    /**
     * @param ContainerInterface $container Container from which to obtain autowired dependencies
     */
    public function __construct(private readonly ContainerInterface $container) {
    }

    /**
     * Autowire parameters of a callable (if any) and call it, returning its return value. By-reference and variadic
     * parameters are not allowed and will throw an exception.
     *
     * @template T of mixed
     * @param callable(mixed...): T $callable A callable
     * @param array<string, mixed> $arguments Named arguments to pass to the callable. Only the remaining parameters
     *                                        will be autowired. Unused arguments are considered an error
     * @return T The return value of the given `$callable`, or `null` for a void function
     * @throws DefinitionException
     * @throws UnsatisfiedDependencyException
     * @throws InvocationException
     * @throws DomainException When an unused argument was given
     * @see Autowirer for semantics
     */
    public function call(callable $callable, array $arguments = []): mixed {
        try {
            $function = new ReflectionFunction($callable instanceof Closure ? $callable : $callable(...));
        } catch (ReflectionException $e) {
            // According to ReflectionFunction documentation, this should not be possible to occur with Closures
            throw new InvalidArgumentException("Failed to reflect Closure", previous: $e);
        }

        $resolvedArgs = $this->resolveFunctionArgs($function, $arguments);

        try {
            return $callable(...$resolvedArgs);
        } catch (Throwable $e) {
            throw new InvocationException("Failed to call autowired callable", previous: $e);
        }
    }

    /**
     * Autowire parameters of a constructor (if any) and create a new instance. The given `$className` must be
     * instantiable, that is, it must be a class, not abstract, and have a public constructor.
     *
     * If autowiring of methods and properties is required, call `autowire()` with the returned object.
     *
     * @template T of object
     * @param class-string<T> $className Fully-qualified name of an instantiable class
     * @param array<string, mixed> $arguments Named arguments to pass to the constructor. Only the remaining
     *                                        parameters will be autowired. Unused arguments are considered an error
     * @return T An instance of the given `$className`
     * @throws DefinitionException
     * @throws UnsatisfiedDependencyException
     * @throws InvocationException
     * @throws InvalidArgumentException When `$className` is not a class-string, i.e. no class with this name exists
     * @throws DomainException When an unused argument was given
     * @see Autowirer::autowire()
     * @see Autowirer for semantics
     */
    public function instantiate(string $className, array $arguments = []): object {
        if (!Util::isClassType($className)) {
            throw new InvalidArgumentException("Autowired class '$className' does not exist or is not a class");
        }

        if (!Util::isInstantiableClass($className)) {
            throw new DefinitionException("Autowired class '$className' is not an instantiable class");
        }

        try {
            $constructor = (new ReflectionClass($className))->getConstructor();
        } catch (ReflectionException $e) {
            // According to ReflectionClass documentation, this should not be able to occur after the check above
            throw new InvalidArgumentException("Failed to reflect Class", previous: $e);
        }

        if ($constructor === null) {
            if (count($arguments) !== 0) {
                throw new DomainException(
                    "Autowired class '$className' does not have a constructor, but argument(s) provided"
                );
            }

            $resolvedArgs = [];
        } else {
            $resolvedArgs = $this->resolveFunctionArgs($constructor, $arguments);
        }

        try {
            return new $className(...$resolvedArgs);
        } catch (Throwable $e) {
            throw new InvocationException("Failed to instantiate autowired class '$className'", previous: $e);
        }
    }

    /**
     * Autowire methods and properties of an object that have the `Autowired` attribute.
     *
     * @throws DefinitionException
     * @throws UnsatisfiedDependencyException
     * @throws InvocationException
     * @see Autowired for semantics
     */
    public function autowire(object $object): void {
        $class = new ReflectionObject($object);

        $properties = $class->getProperties();
        foreach ($properties as $property) {
            if (count($property->getAttributes(Autowired::class)) !== 0) {
                if (!$property->isPublic() || $property->isReadOnly() || $property->isStatic()) {
                    throw new DefinitionException(
                        "Autowired property '{$class->getName()}::{$property->getName()}' is not writable"
                    );
                }

                if ($this->resolveVariable($property, $value)) {
                    // $object->{$property->getName()} = $value;
                    $property->setValue($object, $value);
                }
            }
        }

        $methods = $class->getMethods();
        foreach ($methods as $method) {
            if (count($method->getAttributes(Autowired::class)) !== 0 && !$method->isConstructor()) {
                if (!$method->isPublic() || $method->isAbstract() || $method->isStatic()) {
                    throw new DefinitionException(
                        "Autowired method '{$class->getName()}::{$method->getName()}' is not invokable"
                    );
                }

                try {
                    // resolveFunctionArgs() is in try block so the exception message contains the method name
                    // [ $object, $method->getName() ](...$args);
                    $method->invokeArgs($object, $this->resolveFunctionArgs($method));
                } catch (Throwable $e) {
                    throw new InvocationException(
                        "Failed to call autowired method '{$class->getName()}::{$method->getName()}'",
                        previous: $e
                    );
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $providedArgs
     * @return array<string, ?object>
     * @throws DefinitionException
     * @throws UnsatisfiedDependencyException
     * @throws DomainException
     */
    protected function resolveFunctionArgs(ReflectionFunctionAbstract $function, array $providedArgs = []): array {
        $arguments = [];

        // TODO: Cache parameter lists to avoid reflection API overhead?
        foreach ($function->getParameters() as $parameter) {
            if ($parameter->isPassedByReference() || $parameter->isVariadic()) {
                throw new DefinitionException("Parameters passed by-reference and variadics are not allowed");
            }

            $name = $parameter->getName();

            if (array_key_exists($name, $providedArgs)) {
                // Using the correct type is the responsibility of the caller, we deliberately run into the PHP error
                $arguments[$name] = $providedArgs[$name];
                unset($providedArgs[$name]);
            } elseif ($this->resolveVariable($parameter, $value)) {
                $arguments[$name] = $value;
            }
        }

        if (count($providedArgs) !== 0) {
            throw new DomainException("Leftover argument(s) provided");
        }

        // Note that autowired function calls are valid even if they have no autowireable parameters
        return $arguments;
    }

    /**
     * @param-out ?object $value
     * @return bool `false` if this variable should not be assigned, i.e. retain its default value, or `true` if it
     *              should be set to the value placed in the reference parameter `$value`
     * @throws DefinitionException
     * @throws UnsatisfiedDependencyException
     */
    protected function resolveVariable(
        ReflectionParameter|ReflectionProperty $variable,
        ?object &$value
    ): bool {
        $name = $variable->getName();
        $type = $variable->getType();

        if ($type === null) {
            // Without type hint, the only allowed case is a parameter with a default value available
            // Properties without type hints are not allowed (why even set the Autowired attribute?)
            if ($variable instanceof ReflectionParameter && $variable->isDefaultValueAvailable()) {
                return false;
            }

            throw new DefinitionException("Autowired variable '$name' is missing type");
        } elseif (!($type instanceof ReflectionNamedType) ||
            ($variable instanceof ReflectionProperty && $type->isBuiltin())) {
            // Type is a UnionType, IntersectionType or a built-in type on a property
            // Note that T|null is NOT a UnionType, but a NamedType with allowsNull()
            throw new DefinitionException("Autowired variable '$name' has unsupported type");
        }

        $typeName = $type->getName();

        // isClassType() check also filters out special typehint 'self'
        if (!$type->isBuiltin() && Util::isClassType($typeName) && $this->container->has($typeName)) {
            // This is a class typehint that we can supply; not having it might be OK,
            // but failing to construct it when we do have it should fail
            try {
                $value = $this->container->get($typeName);
                return true;
            } catch (ContainerExceptionInterface $e) {
                throw new UnsatisfiedDependencyException(
                    "Failed obtaining dependency '$typeName' for variable '$name'",
                    previous: $e
                );
            }
        }

        // Dependency is unsatisfied; use default or null if possible
        if (($variable instanceof ReflectionParameter && $variable->isDefaultValueAvailable()) ||
            ($variable instanceof ReflectionProperty && $variable->hasDefaultValue())) {
            return false;
        } elseif ($type->allowsNull()) {
            $value = null;
            return true;
        }

        // Dependency is required
        if ($type->isBuiltin()) {
            throw new DefinitionException("Autowired variable '$name' has unsupported type, but is required");
        } elseif (Util::isClassType($typeName)) {
            throw new UnsatisfiedDependencyException(
                "Autowired variable '$name' dependency is unsatisfied, but required (no default and not nullable)"
            );
        } else {
            throw new DefinitionException("Autowired variable '$name' requires non-existent class {$typeName}");
        }
    }
}
