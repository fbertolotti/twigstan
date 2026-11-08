<?php

declare(strict_types=1);

namespace TwigStan\Error;

use PHPUnit\Framework\TestCase;
use TwigStan\PHPStan\Analysis\Error;

final class ErrorFilterTest extends TestCase
{
    public function testWithoutIgnoresEverythingIsKept(): void
    {
        $errors = [new Error('one'), new Error('two')];

        self::assertSame($errors, (new ErrorFilter([]))->filter($errors));
    }

    public function testIgnoredErrorsAreRemovedAndListIsReindexed(): void
    {
        $keep = new Error('keep');
        $filter = new ErrorFilter([IgnoreError::message('#drop#'), IgnoreError::identifier('other')]);

        self::assertSame(
            [$keep],
            $filter->filter([
                new Error('drop me'),
                $keep,
                new Error('x', identifier: 'other'),
            ]),
        );
    }
}
