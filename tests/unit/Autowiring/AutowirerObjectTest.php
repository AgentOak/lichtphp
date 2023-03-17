<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring;

use DateTimeImmutable;
use InvalidArgumentException;
use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\DefinitionException;
use LichtPHP\Autowiring\InvocationException;
use LichtPHP\Clock\FrozenClock;
use LichtPHP\Container\StaticContainer;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithoutConstructor;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithoutParameters;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithPrivateMethod;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithPrivateProperty;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithReadonlyProperty;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithStaticMethod;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithStaticProperty;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithThrowingMethod;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithUnsupportedProperty;
use LichtPHP\Tests\Autowiring\TestClasses\ClassWithUntypedProperty;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use stdClass;
use Throwable;
use TypeError;

/**
 * Basic tests autowiring different objects with `Autowirer::autowire()`.
 *
 * @see Autowirer::autowire()
 */
class AutowirerObjectTest extends TestCase {
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
     * Basic function test with objects
     */

    #[DoesNotPerformAssertions]
    public function testWorksWithInternalClasses(): void {
        $this->autowirer->autowire(new stdClass());
    }

    #[DoesNotPerformAssertions]
    public function testWorksWithNoopClasses(): void {
        $this->autowirer->autowire(new ClassWithoutConstructor());
    }

    public function testWorksWithPropertiesAndMethods(): void {
        $object = new ClassWithoutParameters();
        $this->autowirer->autowire($object);
        self::assertSame($this->clock, $object->clock);
        self::assertSame($this->clock, $object->getSecondClock());
    }

    public function testCallsMethodsOnceExcludingConstructor(): void {
        $object = new ClassWithoutParameters();
        $this->autowirer->autowire($object);
        // Makes sure constructor is not run a second time
        self::assertSame(1, $object->getConstructorCalls());
        self::assertSame(1, $object->getMethodCalls());
    }

    /*
     * Test autowire-specific failures
     */

    public function testThrowsForPrivateProperty(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not writable");
        $this->autowirer->autowire(new ClassWithPrivateProperty());
    }

    public function testThrowsForStaticProperty(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not writable");
        $this->autowirer->autowire(new ClassWithStaticProperty());
    }

    public function testThrowsForReadonlyProperty(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not writable");
        $this->autowirer->autowire(new ClassWithReadonlyProperty());
    }

    public function testThrowsForPrivateMethod(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not invokable");
        $this->autowirer->autowire(new ClassWithPrivateMethod());
    }

    public function testThrowsForStaticMethod(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("not invokable");
        $this->autowirer->autowire(new ClassWithStaticMethod());
    }

    // This specifically tests the case where type is built-in but nullable, which is only forbidden for properties
    public function testThrowsForPropertyWithUnsupportedType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("unsupported type");
        $this->autowirer->autowire(new ClassWithUnsupportedProperty());
    }

    // This specifically tests the case where type is missing with default, which is only forbidden for properties
    public function testThrowsForPropertyWithMissingType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("missing type");
        $this->autowirer->autowire(new ClassWithUntypedProperty());
    }

    /*
     * Make sure thrown Throwables are wrapped
     */

    public function testWrapsExceptions(): void {
        $expected = new InvalidArgumentException("Test message");
        try {
            $this->autowirer->autowire(new ClassWithThrowingMethod($expected));
            self::fail();
        } catch (Throwable $e) {
            self::assertInstanceOf(InvocationException::class, $e);
            self::assertSame($expected, $e->getPrevious());
        }
    }

    public function testWrapsErrors(): void {
        $expected = new TypeError("Test message");
        try {
            $this->autowirer->autowire(new ClassWithThrowingMethod($expected));
            self::fail();
        } catch (Throwable $e) {
            self::assertInstanceOf(InvocationException::class, $e);
            self::assertSame($expected, $e->getPrevious());
        }
    }
}
