<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use ReflectionClass;

use function React\Async\async;
use function React\Async\await;
use function React\Promise\race;

trait RunTestsInFibersTrait
{
    private const DEFAULT_TIMEOUT_SECONDS = 30;

    /** @var non-empty-string */
    private string $realTestName = 'noop';

    /**
     * @internal
     *
     * @phpstan-ignore-next-line
     */
    final protected function runAsyncTest(mixed ...$args): mixed
    {
        $timeout         = self::DEFAULT_TIMEOUT_SECONDS;
        $reflectionClass = new ReflectionClass($this::class);
        foreach ($reflectionClass->getAttributes() as $classAttribute) {
            $classTimeout = $classAttribute->newInstance();
            if (! ($classTimeout instanceof TimeOutInterface)) {
                continue;
            }

            $timeout = $classTimeout->timeout();
        }

        /**
         * @psalm-suppress InternalMethod
         * @psalm-suppress PossiblyNullArgument
         */
        foreach ($reflectionClass->getMethod($this->realTestName)->getAttributes() as $methodAttribute) {
            $methodTimeout = $methodAttribute->newInstance();
            if (! ($methodTimeout instanceof TimeOutInterface)) {
                continue;
            }

            $timeout = $methodTimeout->timeout();
        }

        $sleepingDeferred = new Deferred();
        $sleepTimer       = Loop::addTimer($timeout, static fn () => $sleepingDeferred->reject(new TimedOut('Test timed out after ' . $timeout . ' second(s)')));

        /**
         * @psalm-suppress MixedArgument
         * @psalm-suppress UndefinedInterfaceMethod
         */
        return await(race([
            async(
                fn (): mixed => ([$this, $this->realTestName])(...$args), /** @phpstan-ignore-line */
            )()->finally(static fn () => Loop::cancelTimer($sleepTimer)),
            $sleepingDeferred->promise(),
        ]));
    }

    final protected function setUp(): void
    {
        $this->realTestName = $this->name();

        /** @phpstan-ignore argument.type */
        $reflectionClass = new ReflectionClass(TestCase::class);
        $property        = $reflectionClass->getProperty('methodName');
        $property->setValue($this, 'runAsyncTest');

        parent::setUp();
    }

    final protected function tearDown(): void
    {
        parent::tearDown();

        /** @phpstan-ignore argument.type */
        $reflectionClass = new ReflectionClass(TestCase::class);
        $property        = $reflectionClass->getProperty('methodName');
        $property->setValue($this, $this->realTestName);
    }
}
