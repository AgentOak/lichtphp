<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\DefinitionException;
use LichtPHP\Autowiring\InvocationException;
use LichtPHP\Clock\FrozenClock;
use LichtPHP\Container\StaticContainer;
use LichtPHP\Tests\Autowiring\TestClasses\AbstractClass;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithoutConstructor;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithoutParameters;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithParameters;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithPrivateConstructor;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithPromotedParameters;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithThrowingConstructor;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use stdClass;
use Throwable;
use TypeError;

/**
 * Basic tests autowiring different classes with `Autowirer::instantiate()`.
 *
 * @see Autowirer::instantiate()
 */
class AutowirerInstantiateTest extends TestCase {
    private const TEST_TIME = 123456789;

    private ClockInterface $clock;
    private Autowirer $autowirer;

    protected function setUp(): void {
        $container = new StaticContainer();
        $this->clock = new FrozenClock(new DateTimeImmutable("@" . self::TEST_TIME));
        $container->set(ClockInterface::class, $this->clock);
        $this->autowirer = new Autowirer($container);
    }

    /*
     * Basic function test with classes
     */

    public function testWorksWithInternalClass(): void {
        $object = $this->autowirer->instantiate(stdClass::class);
        self::assertInstanceOf(stdClass::class, $object);
    }

    public function testWorksWithClassesWithoutConstructor(): void {
        $object = $this->autowirer->instantiate(ClassWithoutConstructor::class);
        self::assertInstanceOf(ClassWithoutConstructor::class, $object);
        self::assertSame("retval", $object->getValue());
    }

    public function testWorksWithClassesWithoutParameters(): void {
        $object = $this->autowirer->instantiate(ClassWithoutParameters::class);
        self::assertInstanceOf(ClassWithoutParameters::class, $object);
        self::assertSame(1, $object->getConstructorCalls());
    }

    public function testWorksWithClassesWithParameters(): void {
        $object = $this->autowirer->instantiate(ClassWithParameters::class);
        self::assertInstanceOf(ClassWithParameters::class, $object);
        self::assertSame($this->clock, $object->getClock());
    }

    public function testWorksWithClassesWithPromotedParameters(): void {
        $object = $this->autowirer->instantiate(ClassWithPromotedParameters::class);
        self::assertInstanceOf(ClassWithPromotedParameters::class, $object);
        self::assertSame($this->clock, $object->getClock());
    }

    /*
     * Test instantiate-specific failures
     */

    public function testThrowsForUninstantiableClassAbstract(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not an instantiable class");
        $this->autowirer->instantiate(AbstractClass::class);
    }

    public function testThrowsForUninstantiableClassPrivateConstructor(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not an instantiable class");
        $this->autowirer->instantiate(ClassWithPrivateConstructor::class);
    }

    public function testThrowsForNonExistentClass(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("does not exist");
        $this->autowirer->instantiate("LichtPHP\\Tests\\Autowiring\\TestClasses\\NonExistentClass");
    }

    public function testThrowsForUnusedArgs(): void {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("argument(s) provided");
        $this->autowirer->instantiate(ClassWithoutConstructor::class, [ "foo" => "bar" ]);
    }

    /*
     * Make sure thrown Throwables are wrapped
     */

    public function testWrapsExceptions(): void {
        $expected = new InvalidArgumentException("Test message");
        try {
            $this->autowirer->instantiate(
                ClassWithThrowingConstructor::class,
                [ "e" => $expected ]
            );
            self::fail();
        } catch (Throwable $e) {
            self::assertInstanceOf(InvocationException::class, $e);
            self::assertSame($expected, $e->getPrevious());
        }
    }

    public function testWrapsErrors(): void {
        $expected = new TypeError("Test message");
        try {
            $this->autowirer->instantiate(
                ClassWithThrowingConstructor::class,
                [ "e" => $expected ]
            );
            self::fail();
        } catch (Throwable $e) {
            self::assertInstanceOf(InvocationException::class, $e);
            self::assertSame($expected, $e->getPrevious());
        }
    }

    /*
     * Make sure returned objects are not autowired automatically
     */

    public function testDoesNotAutowireObjects(): void {
        $object = $this->autowirer->instantiate(ClassWithoutParameters::class);
        self::assertNull($object->clock);
        self::assertNull($object->getSecondClock());
    }
}
