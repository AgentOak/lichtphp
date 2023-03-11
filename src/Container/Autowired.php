<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Attribute;

/**
 * This attribute can be specified on non-static non-readonly public class properties and non-static public class
 * methods. Specifying this attribute on the constructor has no effect, as it necessarily has to be autowired to
 * construct an instance of the class. Likewise, specifying it on promoted properties has no effect. The class should
 * not depend on the order in which autowiring happens, except for the constructor being called first.
 *
 * This attribute only has an effect when the object is autowired through the Autowirer, e.g. when obtaining an instance
 * from an AutowiringContainer.
 *
 * Class methods with this attribute will be called with their parameters autowired. Note that this can also be applied
 * to parameterless methods. By-reference and variadic parameters are not allowed and will throw a
 * ContainerExceptionInterface.
 *
 * Constructor and method parameters that are not typehinted with a class name, but have a default value, are ignored
 * and retain their default value. Otherwise, parameters MUST be typehinted with a class name, or a
 * ContainerExceptionInterface will be thrown. Properties with this attribute MUST always be typehinted with a class
 * name, otherwise a ContainerExceptionInterface will be thrown. Union and intersection types are not supported and will
 * throw a ContainerExceptionInterface.
 *
 * Specifying default values for a parameter or property makes an optional dependency; the Container will try to resolve
 * the dependency but if it is not available, it will use the default value. Additionally, nullable types may be used to
 * specify an optional dependency; if the Container cannot resolve the dependency, it will assign null. Nullable types
 * with a default value will get their default value. If neither the type is nullable, nor a default value is given, the
 * dependency is required and a ContainerExceptionInterface will be thrown if it cannot be satisfied.
 *
 * @see Autowirer
 * @see AutowiringContainer
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Autowired {
    // TODO: Support attribute properties to denominate object name? e.g. cache type?
}
