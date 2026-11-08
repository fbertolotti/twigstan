<?php

declare(strict_types=1);

namespace TwigStan\Error;

use PHPUnit\Framework\TestCase;
use TwigStan\PHPStan\Analysis\Error;
use TwigStan\Twig\SourceLocation;

final class ErrorCollapserTest extends TestCase
{
    public function testNoErrors(): void
    {
        self::assertSame([], (new ErrorCollapser())->collapse([]));
    }

    public function testErrorsWithoutSourceLocationAreAlwaysKept(): void
    {
        $errors = [new Error('message'), new Error('message')];

        self::assertSame($errors, (new ErrorCollapser())->collapse($errors));
    }

    public function testDuplicatesThatAreNotAdjacentAreCollapsed(): void
    {
        $first = $this->error('one', 'a.twig', 1);
        $other = $this->error('two', 'a.twig', 1);
        $duplicate = $this->error('one', 'a.twig', 1);

        self::assertSame([$first, $other], (new ErrorCollapser())->collapse([$first, $other, $duplicate]));
    }

    public function testSameMessageOnOtherLineOrFileIsKept(): void
    {
        $errors = [
            $this->error('one', 'a.twig', 1),
            $this->error('one', 'a.twig', 2),
            $this->error('one', 'b.twig', 1),
        ];

        self::assertSame($errors, (new ErrorCollapser())->collapse($errors));
    }

    public function testKeepsSameCopyRegardlessOfOrder(): void
    {
        $viaB = new Error('one', 'x.php', 1, 'a.twig', new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 5)));
        $viaA = new Error('one', 'y.php', 1, 'a.twig', new SourceLocation('a.twig', 1, new SourceLocation('a.twig', 5)));

        self::assertSame([$viaA], (new ErrorCollapser())->collapse([$viaB, $viaA]));
        self::assertSame([$viaA], (new ErrorCollapser())->collapse([$viaA, $viaB]));
    }

    private function error(string $message, string $file, int $line): Error
    {
        return new Error($message, 'generated.php', 1, $file, new SourceLocation($file, $line));
    }
}
