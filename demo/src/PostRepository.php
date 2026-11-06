<?php

declare(strict_types=1);

namespace TwigStan\Demo;

use DateTimeImmutable;
use TwigStan\Demo\Model\Post;

final class PostRepository
{
    /**
     * @return list<Post>
     */
    public function all(): array
    {
        return [
            new Post(1, 'Hello TwigStan', 'A static analyzer for Twig templates powered by PHPStan.', new DateTimeImmutable('2025-01-10')),
            new Post(2, 'Typed templates', 'The context passed to render() is collected and used to type every variable.', new DateTimeImmutable('2025-02-14')),
        ];
    }

    public function find(int $id): ?Post
    {
        foreach ($this->all() as $post) {
            if ($post->id === $id) {
                return $post;
            }
        }

        return null;
    }
}
