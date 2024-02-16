<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class TimeOut implements TimeOutInterface
{
    public function __construct(
        private readonly int|float $timeout,
    ) {
    }

    public function timeout(): int|float
    {
        return $this->timeout;
    }
}
