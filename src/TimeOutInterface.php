<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

interface TimeOutInterface
{
    public function timeout(): int|float;
}
