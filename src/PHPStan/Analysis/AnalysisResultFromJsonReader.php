<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Analysis;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class AnalysisResultFromJsonReader
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    public function read(string $file, int $exitCode): PHPStanAnalysisResult
    {
        if ( ! file_exists($file)) {
            return new PHPStanAnalysisResult(
                $exitCode,
                [],
                [],
                [
                    'Could not read results from PHPStan. This is most likely caused by a crash in PHPStan during analysis.',
                ],
            );
        }

        $content = $this->filesystem->readFile($file);

        if ( ! json_validate($content)) {
            throw new InvalidArgumentException(sprintf('File "%s" is not a valid JSON file.', $file));
        }

        $result = json_decode($content, true);

        if ( ! is_array($result)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not contain a JSON object.', $file));
        }

        $errors = [];
        foreach (self::arrayOf($result, 'fileSpecificErrors', $file) as $error) {
            if ( ! is_array($error)) {
                throw new InvalidArgumentException(sprintf('File "%s" contains an invalid error.', $file));
            }

            $errors[] = Error::decode($error);
        }

        $collectedData = [];
        foreach (self::arrayOf($result, 'collectedData', $file) as $json) {
            if ( ! is_array($json)) {
                throw new InvalidArgumentException(sprintf('File "%s" contains invalid collected data.', $file));
            }

            // PHPStan groups the data that a collector collected in a file.
            foreach (self::arrayOf($json, 'data', $file) as $data) {
                $collectedData[] = CollectedData::decode([...$json, 'data' => $data]);
            }
        }

        $notFileSpecificErrors = [];
        foreach (self::arrayOf($result, 'notFileSpecificErrors', $file) as $error) {
            if ( ! is_string($error)) {
                throw new InvalidArgumentException(sprintf('File "%s" contains an invalid error.', $file));
            }

            $notFileSpecificErrors[] = $error;
        }

        return new PHPStanAnalysisResult(
            $exitCode,
            $errors,
            $collectedData,
            $notFileSpecificErrors,
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function arrayOf(array $data, string $key, string $file): array
    {
        $value = $data[$key] ?? null;

        if ( ! is_array($value)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not contain the "%s" list.', $file, $key));
        }

        return $value;
    }
}
