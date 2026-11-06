<?php

declare(strict_types=1);

namespace TwigStan\Demo\Model;

use DateTimeImmutable;

final readonly class Post
{
    public function __construct(
        public int $id,
        public string $title,
        public string $body,
        public DateTimeImmutable $publishedAt,
    ) {}

    public function excerpt(int $length = 40): string
    {
        return strlen($this->body) > $length ? substr($this->body, 0, $length) . '...' : $this->body;
    }
}
