<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics\PrometheusPushGateway;

use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use React\EventLoop\Loop;
use WyriHaximus\AsyncTestUtilities\TimeOut;
use WyriHaximus\Metrics\Printer\Prometheus;
use WyriHaximus\Metrics\Registry;

use function React\Async\async;
use function React\Async\await;
use function rtrim;

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

    /** @internal */
    final protected function runAsyncTest(mixed ...$args): mixed
    {
        /**
         * @psalm-suppress InternalMethod
         * @psalm-suppress PossiblyNullArgument
         */
        parent::setName($this->realTestName);

        $timeout         = 30;
//        $reflectionClass = new ReflectionClass($this::class);
//        foreach ($reflectionClass->getAttributes() as $classAttribute) {
//            $classTimeout = $classAttribute->newInstance();
//            if (! ($classTimeout instanceof TimeOut)) {
//                continue;
//            }
//
//            $timeout = $classTimeout->timeout;
//        }
//
//        /**
//         * @psalm-suppress InternalMethod
//         * @psalm-suppress PossiblyNullArgument
//         */
//        foreach ($reflectionClass->getMethod($this->realTestName)->getAttributes() as $methodAttribute) {
//            $methodTimeout = $methodAttribute->newInstance();
//            if (! ($methodTimeout instanceof TimeOut)) {
//                continue;
//            }
//
//            $timeout = $methodTimeout->timeout;
//        }

        $timeout = Loop::addTimer($timeout, static fn () => Loop::stop());

        try {
            /**
             * @psalm-suppress MixedArgument
             * @psalm-suppress UndefinedInterfaceMethod
             */
            return await(async(
                fn (): mixed => ([$this, $this->realTestName])(...$args),
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
