<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use React\EventLoop\Loop;
use React\Promise\Deferred;
use ReflectionClass;

use function React\Async\async;
use function React\Async\await;
use function React\Promise\race;

trait RunTestsInFibersTrait
{
    private const int DEFAULT_TIMEOUT_SECONDS = 30;

    /** @param array<mixed> $testArguments */
    protected function invokeTestMethod(string $methodName, array $testArguments): mixed
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

        foreach ($reflectionClass->getMethod($methodName)->getAttributes() as $methodAttribute) {
            $methodTimeout = $methodAttribute->newInstance();
            if (! ($methodTimeout instanceof TimeOutInterface)) {
                continue;
            }

            $timeout = $methodTimeout->timeout();
        }

        $sleepingDeferred = new Deferred();
        $sleepTimer       = Loop::addTimer($timeout, static fn () => $sleepingDeferred->reject(new TimedOut('Test timed out after ' . $timeout . ' second(s)')));

        return await(race([
            async(
                /** @phpstan-ignore method.dynamicName */
                fn (): mixed => $this->{$methodName}(...$testArguments),
            )()->finally(static fn () => Loop::cancelTimer($sleepTimer)),
            $sleepingDeferred->promise(),
        ]));
    }
}
