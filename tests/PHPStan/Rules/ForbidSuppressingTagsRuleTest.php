<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ForbidSuppressingTagsRule>
 */
final class ForbidSuppressingTagsRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/suppressing-tags.php'], [
            [
                'Do not override the type of a variable with an inline @var tag: type the code instead.',
                16,
            ],
            [
                'Do not ignore PHPStan errors with a comment: fix the code, or ignore the error in the PHPStan configuration.',
                19,
            ],
            // The comment does not ignore the error reported on its own line, only the one on the next line.
            [
                'No error with identifier twigstan.ignoreComment is reported on line 20.',
                20,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new ForbidSuppressingTagsRule();
    }
}
