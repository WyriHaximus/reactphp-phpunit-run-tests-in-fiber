<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\PHPUnit;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use WyriHaximus\React\PHPUnit\RunTestsInFibersTrait;
use WyriHaximus\React\PHPUnit\TimedOut;
use WyriHaximus\React\PHPUnit\TimeOut;

use function React\Async\async;
use function React\Async\await;
use function React\Promise\Timer\sleep;

#[SomeAttribute]
#[TimeOut(0.5)]
#[SomeAttribute]
final class RunTestsInFibersTraitTest extends TestCase
{
    use RunTestsInFibersTrait;

    /** @test */
    public function allTestsAreRanInAFiber(): void
    {
        self::expectOutputString('ab');

        Loop::futureTick(async(static function (): void {
            echo 'a';
        }));

        await(sleep(0.01));

        echo 'b';
    }

    /** @test */
    #[SomeAttribute]
    #[TimeOut(0.1)]
    #[SomeAttribute]
    public function methodLevelTimeout(): void
    {
        self::expectException(TimedOut::class);
        self::expectExceptionMessage('Test timed out after 0.1 second(s)');

        await(sleep(0.2));
    }

    /** @test */
    public function classLevelTimeout(): void
    {
        self::expectException(TimedOut::class);
        self::expectExceptionMessage('Test timed out after 0.5 second(s)');

        await(sleep(0.6));
    }
}
