<?php

declare(strict_types=1);

namespace WyriHaximus\React\PHPUnit;

use RuntimeException;

/**
 * Ignore to allow Runtime Exception extension
 *
 * @phpstan-ignore-next-line
 */
final class TimedOut extends RuntimeException
{
}
