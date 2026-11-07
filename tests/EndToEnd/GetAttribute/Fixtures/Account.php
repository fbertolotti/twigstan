<?php

declare(strict_types=1);

namespace TwigStan\EndToEnd\GetAttribute\Fixtures;

final readonly class Account
{
    public function __construct(
        private string $privateId,
        public string $name,
    ) {}
}
