<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Rules\Data;

final class Example
{
    /**
     * @var list<string>
     */
    private array $allowed = [];

    public function method(): void
    {
        /** @var list<string> $inline */
        $inline = [];

        // @phpstan-ignore twigstan.ignoreComment
        $ignored = [];
    }
}
