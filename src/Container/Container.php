<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use LichtPHP\Autowiring\Autowired;
use LichtPHP\Autowiring\Autowirer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Extends PSR-11: Container Interface and ArrayContainer with on-demand instantiation and autowiring. Always contains
 * itself under all applicable fully-qualified interface and class names, as well as the Autowirer used to autowire
 * instances under the `LichtPHP\Autowiring\Autowirer` name.
 *
 * Entries registered with `link()` or `factory()` are lazy-loaded: they will only be created when they are obtained
 * from `get()` for the first time.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 * @see https://www.php-fig.org/psr/psr-11/meta/
 * @see ContainerInterface
 * @see ArrayContainer
 * @see Autowirer
 * @see Container::registered()
 */
interface Container extends ArrayContainer {
    // TODO: Make __invoke do something useful not covered by ArrayContainer?
    /**
     * Returns whether this Container is autowiring class members after instantiation. If `false`, only the constructor
     * is autowired and `Autowired` attributes on methods and parameters have no effect by default.
     *
     * It is beneficial for performance to turn this off if no class makes use of autowired methods or properties, as
     * the `Autowirer` has to scan through all members of every encountered class for the presence of the `Autowired`
     * attribute.
     *
     * Even if `false`, it is possible to autowire members of a specific object by obtaining the `Autowirer` using the
     * `get()` method and explicitly calling `Autowirer::autowire()` on the object.
     *
     * @see Autowired
     * @see Container::get()
     * @see Autowirer::autowire()
     */
    public function isAutowiringMembers(): bool;

    /**
     * Returns whether an entry for the given `$id` has been registered with `link()` or `factory()`, and therefore an
     * implementation for this `$id` can be obtained from `make()` and `get()`. Specifically, `registered($id)` implies
     * `has($id)`. However, an entry that has only been `set()` can only be obtained from `get()`, but not `make()`.
     *
     * @param class-string $id A fully-qualified non-built-in type name, i.e. the name of a class or interface
     * @see Container::link()
     * @see Container::factory()
     * @see ContainerInterface::has()
     * @see ContainerInterface::set()
     */
    public function registered(string $id): bool;

    /**
     * Create a new instance of a registered entry in this Container. Unlike `get()`, this will create a fresh instance
     * on every call and will not save it into the Container. This only works for entries that have been registered,
     * i.e. `registered($id)` is `true`.
     *
     * @template T of object
     * @param class-string<T> $id A fully-qualified non-built-in type name, i.e. the name of a class or interface
     * @param array<string, mixed> $arguments Named arguments to pass to the constructor. Only the remaining parameters
     *                                        will be autowired. Added to (and potentially overriding) arguments passed
     *                                        to `link()` or `factory()`. Unused arguments are considered an error
     * @return T A new instance of this entry
     * @throws ContainerExceptionInterface If instance could not be created for any reason
     * @throws NotFoundExceptionInterface If entry does not exist or is not a registered entry (i.e. a `set()` entry)
     * @see Container::registered()
     */
    public function make(string $id, array $arguments = []): object;

    /**
     * Register a class name to provide the given `$ids`. The class will be instantiated when an entry with a
     * corresponding ID is obtained from the container with `get()`. All `$ids` will share the same instance of the
     * given class when obtained with `get()`. The created instance object will have its methods and properties
     * autowired if the Container is configured to autowire members.
     *
     * The class may be instantiated again when needed to produce a new instance for `make()`.
     *
     * @param class-string|non-empty-list<class-string> $ids A list of fully-qualified non-built-in type names, i.e. the
     *                                                       name of a class or interface
     * @param class-string $className Fully-qualified name of an instantiable class that implements or extends all
     *                                given `$ids`
     * @param array<string, mixed> $arguments Named arguments to pass to the constructor. Only the remaining parameters
     *                                        will be autowired. Unused arguments are considered an error
     * @throws ContainerExceptionInterface If one of the `$ids` is not a non-built-in type, an entry for one of the
     *                                     `$ids` already exists, the class does not exist or does not implement/extend
     *                                     all of the `$ids`
     * @see ContainerInterface::get()
     * @see Container::make()
     * @see Autowirer::instantiate()
     */
    public function link(string|array $ids, string $className, array $arguments = []): void;

    /**
     * Register a factory method to produce an implementation of the given `$ids`. It will be called when an entry with
     * a corresponding ID is obtained from the container with `get()`. The factory method will only be called once, and
     * all `$ids` will share the produced instance when obtained with `get()`. The returned instance object will have
     * its methods and properties autowired if the Container is configured to autowire members.
     *
     * The factory method may be called again when needed to produce a new instance for `make()`, therefore it has to
     * create a new instance every time it is called.
     *
     * @param class-string|non-empty-list<class-string> $ids A list of fully-qualified non-built-in type names, i.e. the
     *                                                       name of a class or interface
     * @param callable(mixed...): object $factory A callable that produces an instance of a class that implements or
     *                                            extends all given `$ids`
     * @param array<string, mixed> $arguments Named arguments to pass to the callable. Only the remaining parameters
     *                                        will be autowired. Unused arguments are considered an error
     * @throws ContainerExceptionInterface If one of the `$ids` is not a non-built-in type or an entry for one of the
     *                                     `$ids` already exists
     * @see ContainerInterface::get()
     * @see Container::make()
     * @see Autowirer::call()
     */
    public function factory(string|array $ids, callable $factory, array $arguments = []): void;
}
