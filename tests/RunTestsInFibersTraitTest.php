<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\PHPUnit;

use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use WyriHaximus\React\PHPUnit\RunTestsInFibersTrait;
use WyriHaximus\React\PHPUnit\TimeOut;

use function React\Async\async;
use function React\Async\await;
use function React\Promise\Timer\sleep;

#[TimeOut(1)]
final class RunTestsInFibersTraitTest extends TestCase
{
    use RunTestsInFibersTrait;

    #[TimeOut(0.1)]
    public function testAllTestsAreRanInAFiber(): void
    {
        self::expectOutputString('ab');

        Loop::futureTick(async(static function (): void {
            echo 'a';
        }));

        await(sleep(0.01));

        echo 'b';
    }
}
