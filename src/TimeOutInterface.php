<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

/** @api */
interface TimeOutInterface
{
    public function timeout(): int|float;
}
