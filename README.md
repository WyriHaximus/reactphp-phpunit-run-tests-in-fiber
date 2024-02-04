# Trait to run all tests in a fiber

![Continuous Integration](https://github.com/wyrihaximus/reactphp-phpunit-run-tests-in-fiber/workflows/Continuous%20Integration/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/wyrihaximus/react-phpunit-run-tests-in-fiber/v/stable.png)](https://packagist.org/packages/wyrihaximus/react-phpunit-run-tests-in-fiber)
[![Total Downloads](https://poser.pugx.org/wyrihaximus/react-phpunit-run-tests-in-fiber/downloads.png)](https://packagist.org/packages/wyrihaximus/react-phpunit-run-tests-in-fiber/stats)
[![Type Coverage](https://shepherd.dev/github/WyriHaximus/reactphp-phpunit-run-tests-in-fiber/coverage.svg)](https://shepherd.dev/github/WyriHaximus/reactphp-phpunit-run-tests-in-fiber)
[![License](https://poser.pugx.org/wyrihaximus/react-phpunit-run-tests-in-fiber/license.png)](https://packagist.org/packages/wyrihaximus/react-phpunit-run-tests-in-fiber)

# Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require wyrihaximus/react-phpunit-run-tests-in-fiber
```

# Usage

```php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use React\Promise\Promise;
use WyriHaximus\React\PHPUnit\RunTestsInFibersTrait;

use function React\Async\await;

final class SomeTest extends TestCase
{
    use RunTestsInFibersTrait;

    /**
     * @test
     */
    public function happyFlow()
    {
        self::assertTrue(await(new Promise(static function (callable $resolve): void {
            $resolve(true);
        })));
    }
}
```


# License

The MIT License (MIT)

Copyright (c) 2024 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

