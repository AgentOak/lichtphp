<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring;

use DateTimeImmutable;
use InvalidArgumentException;
use LichtPHP\Autowiring\Autowired;
use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\InvocationException;
use LichtPHP\Clock\FrozenClock;
use LichtPHP\Container\StaticContainer;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Throwable;
use TypeError;

/**
 * Basic tests autowiring different types of callables with `Autowirer::call()` and making sure they are called once.
 *
 * @see Autowirer::call()
 */
class AutowirerCallTest extends TestCase {
    private const TEST_TIME = 123456789;

    private Autowirer $autowirer;
    public static int $numCalls = 0;

    protected function setUp(): void {
        $container = new StaticContainer();
        $container->set(ClockInterface::class, new FrozenClock(new DateTimeImmutable("@" . self::TEST_TIME)));
        $this->autowirer = new Autowirer($container);
        self::$numCalls = 0;
    }

    protected function assertPostConditions(): void {
        self::assertSame(1, self::$numCalls);
    }

    /*
     * Example methods
     */

    public function exampleMethod(ClockInterface $clock): int {
        self::$numCalls++;
        return $clock->now()->getTimestamp();
    }

    public function exampleMethodUntyped(ClockInterface $clock) {
        self::$numCalls++;
        return $clock->now()->getTimestamp();
    }

    public function exampleVoid(ClockInterface $clock): void {
        self::$numCalls++;
        $clock->now();
    }

    public function exampleVoidUntyped(ClockInterface $clock) {
        self::$numCalls++;
        $clock->now();
    }

    public static function exampleStatic(ClockInterface $clock): int {
        self::$numCalls++;
        return $clock->now()->getTimestamp();
    }

    private static function exampleStaticPrivate(ClockInterface $clock): int {
        self::$numCalls++;
        return $clock->now()->getTimestamp();
    }

    public function __invoke(ClockInterface $clock): int {
        self::$numCalls++;
        return $clock->now()->getTimestamp();
    }

    public function exampleMethodParameterless(): string {
        self::$numCalls++;
        return "retval";
    }

    /*
     * Basic function test with callables
     */

    public function testWorksWithCallable(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            [ $this, "exampleMethod" ]
        ));
    }

    public function testWorksWithUntypedCallable(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            [ $this, "exampleMethodUntyped" ]
        ));
    }

    public function testWorksWithStaticCallable(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            [ self::class, "exampleStatic" ]
        ));
    }

    public function testReturnsNullForVoidCallable(): void {
        self::assertNull($this->autowirer->call(
            [ $this, "exampleVoid" ]
        ));
    }

    public function testReturnsNullForUntypedVoidCallable(): void {
        self::assertNull($this->autowirer->call(
            [ $this, "exampleVoidUntyped" ]
        ));
    }

    public function testWorksWithParameterlessCallable(): void {
        self::assertSame("retval", $this->autowirer->call(
            [ $this, "exampleMethodParameterless" ]
        ));
    }

    /*
     * Basic function test with closures
     */

    public function testWorksWithClosure(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            $this::exampleMethod(...)
        ));
    }

    public function testWorksWithUntypedClosure(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            $this->exampleMethodUntyped(...)
        ));
    }

    public function testWorksWithStaticClosure(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            self::exampleStaticPrivate(...)
        ));
    }

    public function testWorksWithAnonymousClosure(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            function (ClockInterface $clock) {
                self::$numCalls++;
                return $clock->now()->getTimestamp();
            }
        ));
    }

    public function testReturnsNullForVoidClosure(): void {
        self::assertNull($this->autowirer->call(
            $this->exampleVoid(...)
        ));
    }

    public function testReturnsNullForUntypedVoidClosure(): void {
        self::assertNull($this->autowirer->call(
            $this->exampleVoidUntyped(...)
        ));
    }

    public function testWorksWithParameterlessClosure(): void {
        self::assertSame("retval", $this->autowirer->call(
            $this->exampleMethodParameterless(...)
        ));
    }

    /*
     * Basic function test with invokable objects
     */

    public function testWorksWithInvokableObject(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            $this
        ));
    }

    public function testWorksWithInvokableObjectClosure(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            $this(...)
        ));
    }

    public function testWorksWithAnonymousInvokableObject(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            new class() {
                public function __invoke(ClockInterface $clock) {
                    AutowirerCallTest::$numCalls++;
                    return $clock->now()->getTimestamp();
                }
            }
        ));
    }

    public function testWorksWithAnonymousInvokableObjectClosure(): void {
        self::assertSame(self::TEST_TIME, $this->autowirer->call(
            (new class() {
                public function __invoke(ClockInterface $clock) {
                    AutowirerCallTest::$numCalls++;
                    return $clock->now()->getTimestamp();
                }
            })(...)
        ));
    }

    /*
     * Make sure thrown Throwables are wrapped
     */

    public function testWrapsExceptions(): void {
        $expected = new InvalidArgumentException("Test message");
        try {
            $this->autowirer->call(
                function () use ($expected) {
                    AutowirerCallTest::$numCalls++;
                    throw $expected;
                }
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
            $this->autowirer->call(
                function () use ($expected) {
                    AutowirerCallTest::$numCalls++;
                    throw $expected;
                }
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
        self::assertNull($this->autowirer->call(
            fn() => new class () {
                #[Autowired]
                public ?ClockInterface $clock = null;

                public function __construct() {
                    AutowirerCallTest::$numCalls++;
                }

                public function getClock(): ?ClockInterface {
                    return $this->clock;
                }

                #[Autowired]
                public function run(ClockInterface $clock) {
                    AutowirerCallTest::fail();
                }
            }
        )->getClock());
    }
}
