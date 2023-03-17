<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring;

use DateTimeImmutable;
use DomainException;
use LichtPHP\Autowiring\Autowirer;
use LichtPHP\Autowiring\DefinitionException;
use LichtPHP\Autowiring\UnsatisfiedDependencyException;
use LichtPHP\Clock\FrozenClock;
use LichtPHP\Clock\RequestTimeClock;
use LichtPHP\Clock\SystemClock;
use LichtPHP\Container\ConstructionException;
use LichtPHP\Container\StaticContainer;
use LichtPHP\SimpleCache\Cache;
use LichtPHP\SimpleCache\DummyCache;
use LichtPHP\Tests\Autowiring\TestClasses\ExampleEnumeration;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * More advanced tests checking how `Autowirer` resolves variables. For convenience, uses the `Autowirer::call()`
 * method. Assumes that all `Autowirer` methods share the same resolve mechanism.
 *
 * @see Autowirer::resolveFunctionArgs()
 * @see Autowirer::resolveVariable()
 */
class AutowirerResolveTest extends TestCase {
    private const TEST_TIME = 123456789;

    private StaticContainer $container;
    private Autowirer $autowirer;

    protected function setUp(): void {
        $this->container = new StaticContainer();
        $this->container->set(ClockInterface::class, new FrozenClock(new DateTimeImmutable("@" . self::TEST_TIME)));
        $this->autowirer = new Autowirer($this->container);
    }

    public function testThrowsForMissingType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("missing type");
        $this->autowirer->call(
            fn($param) => "retval"
        );
    }

    public function testAllowsMissingTypeWithDefault(): void {
        self::assertSame(84, $this->autowirer->call(
            fn($param = 42) => $param * 2
        ));
        self::assertNull($this->autowirer->call(
            fn($param = null) => $param
        ));
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn($param = new SystemClock()) => $param
        ));
    }

    public function testAllowsMissingTypeWithProvidedArgs(): void {
        self::assertSame(84, $this->autowirer->call(
            fn($param) => $param * 2,
            [ "param" => 42 ]
        ));
    }

    public function testThrowsForNonClassType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("unsupported type");
        $this->autowirer->call(
            fn(int $param) => "retval"
        );
    }

    public function testThrowsForNonClassTypeObject(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("unsupported type");
        $this->autowirer->call(
            fn(object $param) => "retval"
        );
    }

    public function testAllowsNonClassTypeWithDefault(): void {
        self::assertSame(84, $this->autowirer->call(
            fn(int $param = 42) => $param * 2
        ));
        self::assertSame("retval", $this->autowirer->call(
            fn(string $param = "val") => "ret$param"
        ));
        self::assertSame(1.0, $this->autowirer->call(
            fn(float $param = 1.0) => $param
        ));
        self::assertFalse($this->autowirer->call(
            fn(bool $param = true) => !$param
        ));
        self::assertSame("retval", $this->autowirer->call(
            fn(array $param = [ "retval" ]) => $param[0]
        ));
        self::assertInstanceOf(stdClass::class, $this->autowirer->call(
            fn(object $param = new stdClass()) => $param
        ));
        // Both default values and constants may not be expressions, so null is the only possible default for callable
        self::assertNull($this->autowirer->call(
            fn(callable $param = null) => $param
        ));
        //self::assertTrue($this->autowirer->call(
        //    fn(true $param = true) => $param
        //));
        //self::assertFalse($this->autowirer->call(
        //    fn(false $param = false) => $param
        //));
        //self::assertNull($this->autowirer->call(
        //    fn(null $param = null) => $param
        //));
    }

    public function testAllowsNullableNonClassType(): void {
        self::assertNull($this->autowirer->call(
            fn(?int $param) => $param
        ));
        self::assertNull($this->autowirer->call(
            fn(?string $param) => $param
        ));
        self::assertNull($this->autowirer->call(
            fn(?float $param) => $param
        ));
        self::assertNull($this->autowirer->call(
            fn(?bool $param) => $param
        ));
        self::assertNull($this->autowirer->call(
            fn(?array $param) => $param
        ));
        self::assertNull($this->autowirer->call(
            fn(?object $param) => $param
        ));
        self::assertNull($this->autowirer->call(
            fn(?callable $param) => $param
        ));
        //self::assertNull($this->autowirer->call(
        //    fn(?true $param) => $param
        //));
        //self::assertNull($this->autowirer->call(
        //    fn(?false $param) => $param
        //));
    }

    public function testAllowsNonClassTypeWithProvidedArgs(): void {
        self::assertSame(84, $this->autowirer->call(
            fn(int $param) => $param * 2,
            [ "param" => 42 ]
        ));
    }

    public function testThrowsForSelfType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("unsupported type");
        $this->autowirer->call(
            fn(self $param) => $param
        );
    }

    public function testThrowsForUnionType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("unsupported type");
        $this->autowirer->call(
            fn(ClockInterface|ContainerInterface|null $param = null) => "retval"
        );
    }

    public function testThrowsForIntersectionType(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("unsupported type");
        $this->autowirer->call(
            fn(ClockInterface&ContainerInterface $param) => "retval"
        );
    }

    public function testAllowsUnsupportedTypesWithProvidedArgs(): void {
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(ClockInterface|ContainerInterface $param) => $param,
            [ "param" => new SystemClock() ]
        ));
        self::assertNotNull($this->autowirer->call(
            fn(ClockInterface&ContainerInterface $param) => $param,
            [ "param" =>
                $this->createStubForIntersectionOfInterfaces([ ClockInterface::class, ContainerInterface::class ]) ]
        ));
    }

    public function testThrowsForVariadics(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("variadic");
        $this->autowirer->call(
            fn(ClockInterface ...$param) => "retval"
        );
    }

    public function testThrowsForReferences(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("by-reference");
        $this->autowirer->call(
            fn(ClockInterface &$param) => "retval"
        );
    }

    public function testPrefersDefaultsOverNullable(): void {
        self::assertSame(84, $this->autowirer->call(
            fn(?int $param = 42) => $param * 2
        ));
        self::assertInstanceOf(DummyCache::class, $this->autowirer->call(
            fn(?Cache $param = new DummyCache(new SystemClock())) => $param
        ));
    }

    public function testPrefersContainerOverOptionals(): void {
        self::assertInstanceOf(FrozenClock::class, $this->autowirer->call(
            fn(?ClockInterface $clock) => $clock
        ));
        self::assertInstanceOf(FrozenClock::class, $this->autowirer->call(
            fn(ClockInterface $clock = new SystemClock()) => $clock
        ));
        self::assertInstanceOf(FrozenClock::class, $this->autowirer->call(
            fn(?ClockInterface $clock = new SystemClock()) => $clock
        ));
    }

    public function testPrefersProvidedArgs(): void {
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(ClockInterface $clock) => $clock,
            [ "clock" => new SystemClock() ]
        ));
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(?ClockInterface $clock) => $clock,
            [ "clock" => new SystemClock() ]
        ));
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(ClockInterface $clock = new RequestTimeClock()) => $clock,
            [ "clock" => new SystemClock() ]
        ));
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(?ClockInterface $clock = new RequestTimeClock()) => $clock,
            [ "clock" => new SystemClock() ]
        ));
    }

    public function testAllowsEnumType(): void {
        self::assertSame(ExampleEnumeration::FIRST->value, $this->autowirer->call(
            fn(ExampleEnumeration $ex) => $ex->value,
            [ "ex" => ExampleEnumeration::FIRST ]
        ));
        self::assertSame(ExampleEnumeration::SECOND->value, $this->autowirer->call(
            fn(ExampleEnumeration $ex = ExampleEnumeration::SECOND) => $ex->value
        ));
        self::assertSame(ExampleEnumeration::THIRD->value, $this->autowirer->call(
            fn(?ExampleEnumeration $ex) => $ex->value,
            [ "ex" => ExampleEnumeration::THIRD ]
        ));
        $this->container->set(ExampleEnumeration::class, ExampleEnumeration::FOURTH);
        self::assertSame(ExampleEnumeration::FOURTH->value, $this->autowirer->call(
            fn(ExampleEnumeration $ex) => $ex->value
        ));
    }

    // All other tests use interfaces so make sure we don't miss this
    public function testAllowsConcreteClassType(): void {
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(SystemClock $clock) => $clock,
            [ "clock" => new SystemClock() ]
        ));
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(SystemClock $clock = new SystemClock()) => $clock
        ));
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(?SystemClock $clock) => $clock,
            [ "clock" => new SystemClock() ]
        ));
        $this->container->set(SystemClock::class, new SystemClock());
        self::assertInstanceOf(SystemClock::class, $this->autowirer->call(
            fn(SystemClock $clock) => $clock
        ));
    }

    public function testThrowsForUnsatisfiedDependency(): void {
        $this->expectException(UnsatisfiedDependencyException::class);
        $this->expectExceptionMessage("dependency is unsatisfied, but required");
        $this->autowirer->call(
            fn(Cache $cache) => $cache
        );
    }

    public function testThrowsForDependencyError(): void {
        $this->expectException(UnsatisfiedDependencyException::class);
        $this->expectExceptionMessage("Failed obtaining dependency");
        $container = $this->createStub(ContainerInterface::class);
        $container->method("has")->willReturnCallback(fn(string $id) => $id === Cache::class);
        $container->method("get")->willThrowException(new ConstructionException());
        // Specifically make sure that this occurs for optional dependencies
        (new Autowirer($container))->call(
            fn(?Cache $cache) => $cache
        );
    }

    public function testThrowsForNonExistentDependency(): void {
        $this->expectException(DefinitionException::class);
        $this->expectExceptionMessage("requires non-existent class");
        $this->autowirer->call(
            fn(NonExistentClass $cache) => $cache
        );
    }

    public function testAllowsOptionalNonExistentDependency(): void {
        self::assertNull($this->autowirer->call(
            fn(?NonExistentClass $cache) => $cache
        ));
    }

    public function testWorkWithMultipleParameters(): void {
        $this->assertSame(self::TEST_TIME + 7 + 11 + 17, $this->autowirer->call(
            function (ClockInterface $clock, ?Cache $cache, bool $addSeven, ?int $value = 17) {
                return $clock->now()->getTimestamp() + ($addSeven ? 7 : 0) + ($cache === null ? 11 : 13) + $value;
            },
            [ "addSeven" => true ]
        ));
        $this->assertSame(self::TEST_TIME * 2, $this->autowirer->call(
            function (ClockInterface $clock, ?ClockInterface $secondClock) {
                return $clock->now()->getTimestamp() + ($secondClock?->now()?->getTimestamp() ?? 0);
            }
        ));
    }

    public function testThrowsForUnusedArgsParameterless(): void {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("argument(s) provided");
        $this->autowirer->call(
            fn() => "retval",
            [ "foo" => "bar" ]
        );
    }

    public function testThrowsForUnusedArgs(): void {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("argument(s) provided");
        $this->autowirer->call(
            fn(ClockInterface $clock) => $clock,
            [ "bar" ]
        );
    }

    public function testThrowsForUnusedArgsMultipleParameters(): void {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("argument(s) provided");
        $this->autowirer->call(
            fn(ClockInterface $clock, ?Cache $cache, bool $addSeven, ?int $value = 17) => "retval",
            [ "addSeven" => true, "foo" => "bar" ]
        );
    }
}
