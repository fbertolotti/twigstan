<?php

declare(strict_types=1);

namespace TwigStan\EndToEnd\MoreTags;

use TwigStan\EndToEnd\AbstractEndToEndTestCase;

final class MoreTagsTest extends AbstractEndToEndTestCase
{
    public function test(): void
    {
        parent::runAnalysis(__DIR__);
    }
}
