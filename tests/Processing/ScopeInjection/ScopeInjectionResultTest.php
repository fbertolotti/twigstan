<?php

declare(strict_types=1);

namespace TwigStan\Processing\ScopeInjection;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TwigStan\Twig\SourceLocation;

final class ScopeInjectionResultTest extends TestCase
{
    public function testSourceLocationFallsBackToThePreviousMappedLine(): void
    {
        $one = new SourceLocation('a.twig', 1);
        $five = new SourceLocation('a.twig', 2);
        $result = new ScopeInjectionResult('a.twig', 'a.php', [3 => $one, 7 => $five]);

        self::assertSame($one, $result->getSourceLocationForPhpLine(3));
        self::assertSame($one, $result->getSourceLocationForPhpLine(6));
        self::assertSame($five, $result->getSourceLocationForPhpLine(7));
        self::assertSame($five, $result->getSourceLocationForPhpLine(100));
        self::assertNull($result->getSourceLocationForPhpLine(2));
    }

    public function testCollectionLookups(): void
    {
        $a = new ScopeInjectionResult('a.twig', 'a.php', []);
        $b = new ScopeInjectionResult('b.twig', 'b.php', []);
        $collection = new ScopeInjectionResultCollection($a, $b);

        self::assertTrue($collection->hasTwigFileName('a.twig'));
        self::assertFalse($collection->hasTwigFileName('c.twig'));
        self::assertSame($b, $collection->getByTwigFileName('b.twig'));
        self::assertTrue($collection->hasPhpFile('a.php'));
        self::assertFalse($collection->hasPhpFile('c.php'));
        self::assertSame($a, $collection->getByPhpFile('a.php'));
        self::assertSame(['a.twig' => $a, 'b.twig' => $b], iterator_to_array($collection, true));
    }

    public function testCollectionThrowsForUnknownEntries(): void
    {
        $collection = new ScopeInjectionResultCollection();

        try {
            $collection->getByTwigFileName('x.twig');
            self::fail('Expected exception.');
        } catch (InvalidArgumentException $e) {
            self::assertStringContainsString('x.twig', $e->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $collection->getByPhpFile('x.php');
    }

    public function testWithAddsResultsWithoutMutating(): void
    {
        $a = new ScopeInjectionResult('a.twig', 'a.php', []);
        $b = new ScopeInjectionResult('b.twig', 'b.php', []);
        $original = new ScopeInjectionResultCollection($a);

        $extended = $original->with($b);

        self::assertFalse($original->hasTwigFileName('b.twig'));
        self::assertSame($b, $extended->getByTwigFileName('b.twig'));
        self::assertSame($a, $extended->getByTwigFileName('a.twig'));
    }
}
