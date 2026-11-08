<?php

declare(strict_types=1);

namespace TwigStan\EndToEnd\Demo;

use TwigStan\EndToEnd\AbstractEndToEndTestCase;

/**
 * Guards the demo application: the expected errors live next to it, in demo/errors.json.
 */
final class DemoTest extends AbstractEndToEndTestCase
{
    public function test(): void
    {
        parent::runAnalysis(dirname(__DIR__, 3) . '/demo');
    }

    protected function getConfigurationFile(): string
    {
        return dirname(__DIR__, 3) . '/demo/twigstan.php';
    }
}
