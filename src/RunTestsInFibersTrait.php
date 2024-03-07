<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use React\EventLoop\Loop;
use React\Promise\Deferred;
use ReflectionClass;

use function assert;
use function is_string;
use function React\Async\async;
use function React\Async\await;
use function React\Promise\race;

trait RunTestsInFibersTrait
{
    private string|null $realTestName = null;

    /** @codeCoverageIgnore Invoked before code coverage data is being collected. */
    final public function setName(string $name): void
    {
        /** @psalm-suppress InternalMethod */
        parent::setName($name);

        $this->realTestName = $name;
    }

    /**
     * @internal
     *
     * @phpstan-ignore-next-line
     */
    final protected function runAsyncTest(mixed ...$args): mixed
    {
        assert(is_string($this->realTestName));

        /**
         * @psalm-suppress InternalMethod
         * @psalm-suppress PossiblyNullArgument
         */
        parent::setName($this->realTestName);

        assert(is_string($this->realTestName));

        $timeout         = 60;
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
            )()->always(static fn () => Loop::cancelTimer($sleepTimer)),
            $sleepingDeferred->promise(),
        ]));
    }

    final protected function runTest(): mixed
    {
        /** @psalm-suppress InternalMethod */
        parent::setName('runAsyncTest');

        return parent::runTest();
    }
}
