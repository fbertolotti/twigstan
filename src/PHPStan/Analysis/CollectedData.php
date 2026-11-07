<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Analysis;

use InvalidArgumentException;

final readonly class CollectedData
{
    private function __construct(
        public mixed $data,
        public string $filePath,
        public string $collecterType,
    ) {}

    /**
     * @param array<mixed> $json
     */
    public static function decode(array $json): self
    {
        $filePath = $json['filePath'] ?? null;
        $collectorType = $json['collectorType'] ?? null;

        if ( ! is_string($filePath) || ! is_string($collectorType)) {
            throw new InvalidArgumentException('The collected data is invalid.');
        }

        return new self(
            $json['data'] ?? null,
            $filePath,
            $collectorType,
        );
    }
}
