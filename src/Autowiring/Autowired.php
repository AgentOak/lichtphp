<?php
declare(strict_types=1);

namespace LichtPHP\Autowiring;

use Attribute;

/**
 * This attribute can be specified on non-static non-readonly public class properties and non-static public class
 * methods. Specifying this attribute on the constructor has no effect, as it necessarily has to be autowired to
 * construct an instance of the class. Likewise, specifying it on promoted properties has no effect. The class should
 * not depend on the order in which autowiring happens, except for the constructor being called first.
 *
 * This attribute only has an effect when the object is autowired through `Autowirer::autowire()`, e.g. when obtaining
 * an instance from an `AutowiringContainer` that is configured to autowire members.
 *
 * Class methods with this attribute will be called with their parameters autowired. Note that this can also be applied
 * to parameterless methods.
 *
 * @see Autowirer for semantics
 * @see Autowirer::autowire()
 * @see AutowiringContainer
 * @see Container::isAutowiringMembers()
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Autowired {
    // TODO: Allow specifying on parameters so same semantics as for properties apply
    // TODO: Property to specify ID other than class FQN? In subtype of Autowired?
    // TODO: make() attribute or subtype to force creation of fresh instance
}
