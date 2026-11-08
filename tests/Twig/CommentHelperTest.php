<?php

declare(strict_types=1);

namespace TwigStan\Twig;

use PhpParser\Comment;
use PHPUnit\Framework\TestCase;

final class CommentHelperTest extends TestCase
{
    public function testCommentWithoutLinePrefixIsIgnored(): void
    {
        self::assertNull(CommentHelper::getSourceLocationFromComment('// something else'));
        self::assertNull(CommentHelper::getSourceLocationFromComment('/* line a.twig:1 */'));
    }

    public function testSingleLocation(): void
    {
        self::assertEquals(
            new SourceLocation('a.twig', 12),
            CommentHelper::getSourceLocationFromComment('// line a.twig:12'),
        );
    }

    public function testMultipleLocationsKeepTheirOrder(): void
    {
        self::assertEquals(
            new SourceLocation('a.twig', 1, new SourceLocation('b.twig', 2)),
            CommentHelper::getSourceLocationFromComment('// line a.twig:1, b.twig:2'),
        );
    }

    public function testFirstMatchingCommentWins(): void
    {
        $result = CommentHelper::getSourceLocationFromComments([
            new Comment('// unrelated'),
            new Comment('// line a.twig:3'),
            new Comment('// line b.twig:4'),
        ]);

        self::assertEquals(new SourceLocation('a.twig', 3), $result);
    }

    public function testNoMatchingComments(): void
    {
        self::assertNull(CommentHelper::getSourceLocationFromComments([]));
        self::assertNull(CommentHelper::getSourceLocationFromComments([new Comment('// unrelated')]));
    }
}
