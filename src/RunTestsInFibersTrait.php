<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use React\EventLoop\Loop;
use ReflectionClass;

use function assert;
use function is_string;
use function React\Async\async;
use function React\Async\await;

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

        $timeout         = 30;
        $reflectionClass = new ReflectionClass($this::class);
        foreach ($reflectionClass->getAttributes() as $classAttribute) {
            $classTimeout = $classAttribute->newInstance();
            if (! ($classTimeout instanceof TimeOut)) {
                continue;
            }

            $timeout = $classTimeout->timeout;
        }

        /**
         * @psalm-suppress InternalMethod
         * @psalm-suppress PossiblyNullArgument
         */
        foreach ($reflectionClass->getMethod($this->realTestName)->getAttributes() as $methodAttribute) {
            $methodTimeout = $methodAttribute->newInstance();
            if (! ($methodTimeout instanceof TimeOut)) {
                continue;
            }

            $timeout = $methodTimeout->timeout;
        }

        $timeout = Loop::addTimer($timeout, static fn () => Loop::stop());

        try {
            /**
             * @psalm-suppress MixedArgument
             * @psalm-suppress UndefinedInterfaceMethod
             */
            return await(async(
                fn (): mixed => ([$this, $this->realTestName])(...$args), /** @phpstan-ignore-line */
            )());
        } finally {
            Loop::cancelTimer($timeout);
        }
    }

    final protected function runTest(): mixed
    {
        /** @psalm-suppress InternalMethod */
        parent::setName('runAsyncTest');

        return parent::runTest();
    }
}
