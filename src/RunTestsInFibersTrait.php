<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use React\Promise\PromiseInterface;
use ReflectionClass;

use function assert;
use function is_string;
use function React\Async\async;
use function React\Async\await;
use function React\Promise\race;
use function React\Promise\reject;
use function React\Promise\Timer\sleep;

trait RunTestsInFibersTrait
{
    private const DEFAULT_TIMEOUT_SECONDS = 30;

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

        /**
         * @psalm-suppress MixedArgument
         * @psalm-suppress UndefinedInterfaceMethod
         */
        return await(race([
            async(
                fn (): mixed => ([$this, $this->realTestName])(...$args), /** @phpstan-ignore-line */
            )(),
            sleep($timeout)->then(static fn (): PromiseInterface => reject(new TimedOut('Test timed out after ' . $timeout . ' second(s)'))),
        ]));
    }

    final protected function runTest(): mixed
    {
        /** @psalm-suppress InternalMethod */
        parent::setName('runAsyncTest');

        return parent::runTest();
    }
}
