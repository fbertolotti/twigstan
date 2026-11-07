<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Analysis;

use InvalidArgumentException;
use TwigStan\Twig\SourceLocation;

final readonly class Error
{
    public function __construct(
        public string $message,
        public ?string $phpFile = null,
        public int $phpLine = 0,
        public ?string $twigFile = null,
        public ?SourceLocation $sourceLocation = null,
        public ?string $identifier = null,
        public ?string $tip = null,
        public bool $canBeIgnored = true,
    ) {}

    /**
     * @param array<mixed> $json
     */
    public static function decode(array $json): self
    {
        $message = $json['message'] ?? null;
        $file = $json['file'] ?? null;
        $line = $json['line'] ?? 0;
        $identifier = $json['identifier'] ?? null;
        $tip = $json['tip'] ?? null;
        $canBeIgnored = $json['canBeIgnored'] ?? true;

        if (
            ! is_string($message)
            || ($file !== null && ! is_string($file))
            || ! is_int($line)
            || ($identifier !== null && ! is_string($identifier))
            || ($tip !== null && ! is_string($tip))
            || ! is_bool($canBeIgnored)
        ) {
            throw new InvalidArgumentException('The PHPStan error data is invalid.');
        }

        return new self(
            $message,
            $file,
            $line,
            null,
            null,
            $identifier,
            $tip,
            $canBeIgnored,
        );
    }

    public function withMessage(string $message): self
    {
        return new self(
            $message,
            $this->phpFile,
            $this->phpLine,
            $this->twigFile,
            $this->sourceLocation,
            $this->identifier,
            $this->tip,
        );
    }

    public function withTwigFileAndSourceLocation(string $twigFile, SourceLocation $sourceLocation): self
    {
        return new self(
            $this->message,
            $this->phpFile,
            $this->phpLine,
            $twigFile,
            $sourceLocation,
            $this->identifier,
            $this->tip,
        );
    }
}
