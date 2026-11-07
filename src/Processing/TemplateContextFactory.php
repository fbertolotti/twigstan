<?php

declare(strict_types=1);

namespace TwigStan\Processing;

use LogicException;
use TwigStan\PHPStan\Analysis\PHPStanAnalysisResult;
use TwigStan\PHPStan\Collector\TemplateContextCollector;
use TwigStan\Twig\SourceLocation;
use TwigStan\Twig\TwigFileCanonicalizer;

final readonly class TemplateContextFactory
{
    public function __construct(
        private TwigFileCanonicalizer $twigFileCanonicalizer,
    ) {}

    public function create(PHPStanAnalysisResult $analysisResult): TemplateContext
    {
        /**
         * @var array<string, array<string, array{SourceLocation, string}>> $templateContext
         */
        $templateContext = [];
        foreach ($analysisResult->collectedData as $data) {
            if (is_a($data->collecterType, TemplateContextCollector::class, true)) {
                if ( ! is_array($data->data)) {
                    throw new LogicException('The collected template contexts are invalid.');
                }

                foreach ($data->data as $renderData) {
                    if ( ! is_array($renderData)) {
                        throw new LogicException('The collected template context is invalid.');
                    }

                    $templateName = $renderData['template'] ?? null;
                    $macro = $renderData['macro'] ?? null;
                    $sourceLocationData = $renderData['sourceLocation'] ?? null;
                    $context = $renderData['context'] ?? null;

                    if (
                        ! is_string($templateName)
                        || ($macro !== null && ! is_string($macro))
                        || ! is_array($sourceLocationData)
                        || ! is_string($context)
                    ) {
                        throw new LogicException('The collected template context is invalid.');
                    }

                    $template = $this->twigFileCanonicalizer->absolute($templateName);

                    if ($macro !== null) {
                        $template = TemplateContext::macroKey($template, $macro);
                    }

                    $sourceLocation = SourceLocation::decode($sourceLocationData);

                    $templateContext[$template][$sourceLocation->getHash()] = [$sourceLocation, $context];
                }
            }
        }

        return new TemplateContext($templateContext);
    }
}
