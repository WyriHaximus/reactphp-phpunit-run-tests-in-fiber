<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\React\PHPUnit;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class SomeAttribute
{
}
