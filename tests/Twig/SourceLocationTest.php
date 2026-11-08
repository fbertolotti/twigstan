<?php

declare(strict_types=1);

namespace TwigStan\Twig;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SourceLocationTest extends TestCase
{
    public function testAppendToNullReturnsTheAppendedLocation(): void
    {
        $toAppend = new SourceLocation('a.twig', 1);

        self::assertSame($toAppend, SourceLocation::append(null, $toAppend));
    }

    public function testAppendAddsLocationAtTheEndOfTheChain(): void
    {
        $current = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));

        $result = SourceLocation::append($current, new SourceLocation('c.twig', 3));

        self::assertSame('a.twig:1, b.twig:2, c.twig:3', $result->toString());
    }

    public function testAppendIgnoresLocationEqualToTheLastOne(): void
    {
        $current = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));

        $result = SourceLocation::append($current, new SourceLocation('b.twig', 2));

        self::assertSame($current, $result);
    }

    public function testDecode(): void
    {
        $result = SourceLocation::decode([
            'fileName' => 'a.twig',
            'lineNumber' => 1,
            'previous' => ['fileName' => 'b.twig', 'lineNumber' => 2, 'previous' => null],
        ]);

        self::assertEquals(new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2)), $result);
    }

    /**
     * @param array<mixed> $data
     */
    #[DataProvider('provideInvalidData')]
    public function testDecodeRejectsInvalidData(array $data): void
    {
        $this->expectException(InvalidArgumentException::class);

        SourceLocation::decode($data);
    }

    /**
     * @return iterable<string, array{array<mixed>}>
     */
    public static function provideInvalidData(): iterable
    {
        yield 'missing file name' => [['lineNumber' => 1]];
        yield 'missing line number' => [['fileName' => 'a.twig']];
        yield 'line number is a string' => [['fileName' => 'a.twig', 'lineNumber' => '1']];
        yield 'previous is not an array' => [['fileName' => 'a.twig', 'lineNumber' => 1, 'previous' => 'x']];
    }

    public function testToString(): void
    {
        $location = new SourceLocation('/project/templates/a.twig', 1, new SourceLocation('/project/templates/b.twig', 2));

        self::assertSame('/project/templates/a.twig:1, /project/templates/b.twig:2', $location->toString());
        self::assertSame('/project/templates/a.twig:1, /project/templates/b.twig:2', (string) $location);
        self::assertSame('templates/a.twig:1, templates/b.twig:2', $location->toString('/project'));
    }

    public function testContains(): void
    {
        $location = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));

        self::assertTrue($location->contains('a.twig'));
        self::assertTrue($location->contains('b.twig'));
        self::assertFalse($location->contains('c.twig'));
    }

    public function testLast(): void
    {
        $last = new SourceLocation('b.twig', 2);
        $location = new SourceLocation('a.twig', 1, $last);

        self::assertSame($last, $location->last());
        self::assertSame($last, $last->last());
    }

    public function testIterationWalksTheChain(): void
    {
        $location = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));

        self::assertSame(
            ['a.twig:1', 'b.twig:2'],
            array_map(fn(SourceLocation $item) => $item->fileName . ':' . $item->lineNumber, iterator_to_array($location, false)),
        );
    }

    public function testHashDependsOnTheWholeChain(): void
    {
        $one = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));
        $same = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2));
        $other = new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 3));

        self::assertSame($one->getHash(), $same->getHash());
        self::assertNotSame($one->getHash(), $other->getHash());
    }

    public function testSort(): void
    {
        $a1 = new SourceLocation('a.twig', 1);
        $a2 = new SourceLocation('a.twig', 2);
        $b1 = new SourceLocation('b.twig', 1);

        self::assertSame(-1, $a1->sort($a2));
        self::assertSame(1, $a2->sort($a1));
        self::assertSame(0, $a1->sort(new SourceLocation('a.twig', 1)));
        self::assertSame(-1, $a2->sort($b1));
        self::assertSame(1, $b1->sort($a1));
    }
}
