<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Analysis;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TwigStan\Twig\SourceLocation;

final class ErrorTest extends TestCase
{
    public function testDecodeFullError(): void
    {
        $error = Error::decode([
            'message' => 'm',
            'file' => 'f.php',
            'line' => 3,
            'identifier' => 'id',
            'tip' => 't',
            'canBeIgnored' => false,
        ]);

        self::assertEquals(new Error('m', 'f.php', 3, null, null, 'id', 't', false), $error);
    }

    public function testDecodeAppliesDefaults(): void
    {
        self::assertEquals(new Error('m'), Error::decode(['message' => 'm']));
    }

    public function testDecodeRejectsInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Error::decode(['message' => 'm', 'line' => '3']);
    }

    public function testDecodeRequiresMessage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Error::decode([]);
    }

    public function testWithTwigFileAndSourceLocation(): void
    {
        $location = new SourceLocation('a.twig', 4);
        $error = (new Error('m', 'f.php', 3, null, null, 'id', 't'))->withTwigFileAndSourceLocation('a.twig', $location);

        self::assertSame('a.twig', $error->twigFile);
        self::assertSame($location, $error->sourceLocation);
        self::assertSame('m', $error->message);
        self::assertSame('id', $error->identifier);
        self::assertSame('f.php', $error->phpFile);
    }

    public function testWithMessageKeepsTheRest(): void
    {
        $location = new SourceLocation('a.twig', 4);
        $error = (new Error('m', 'f.php', 3, 'a.twig', $location, 'id', 't'))->withMessage('new');

        self::assertSame('new', $error->message);
        self::assertSame($location, $error->sourceLocation);
        self::assertSame('t', $error->tip);
    }
}
