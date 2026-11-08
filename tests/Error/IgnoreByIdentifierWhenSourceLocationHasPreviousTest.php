<?php

declare(strict_types=1);

namespace TwigStan\Error;

use PHPUnit\Framework\TestCase;
use TwigStan\PHPStan\Analysis\Error;
use TwigStan\Twig\SourceLocation;

final class IgnoreByIdentifierWhenSourceLocationHasPreviousTest extends TestCase
{
    public function testIgnoresMatchingIdentifierWhenLocationHasPrevious(): void
    {
        $ignore = new IgnoreByIdentifierWhenSourceLocationHasPrevious(['foo.bar']);
        $location = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));

        self::assertTrue($ignore->shouldIgnore(new Error('m', sourceLocation: $location, identifier: 'foo.bar')));
        self::assertFalse($ignore->shouldIgnore(new Error('m', sourceLocation: $location, identifier: 'other')));
        self::assertFalse($ignore->shouldIgnore(new Error('m', sourceLocation: $location)));
    }

    public function testKeepsErrorsWithoutPreviousLocation(): void
    {
        $ignore = new IgnoreByIdentifierWhenSourceLocationHasPrevious(['foo.bar']);

        self::assertFalse($ignore->shouldIgnore(new Error('m', sourceLocation: new SourceLocation('a.twig', 1), identifier: 'foo.bar')));
        self::assertFalse($ignore->shouldIgnore(new Error('m', identifier: 'foo.bar')));
    }
}
