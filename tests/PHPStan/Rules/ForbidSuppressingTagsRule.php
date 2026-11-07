<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\Rules;

use PhpParser\Comment;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\VirtualNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbids inline variable type tags and comments that ignore PHPStan errors.
 *
 * Only used to analyse TwigStan itself, it is not part of the shipped code.
 *
 * @implements Rule<Node>
 */
final class ForbidSuppressingTagsRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Virtual nodes copy the attributes, and so the comments, of the node they wrap.
        if ($node instanceof VirtualNode) {
            return [];
        }

        // The type of a property, a class constant or a promoted property is declared where it is declared.
        $isDeclaration = $node instanceof Node\Stmt\Property
            || $node instanceof Node\Stmt\ClassConst
            || $node instanceof Node\Stmt\EnumCase
            || $node instanceof Node\Param;

        $errors = [];
        foreach ($node->getComments() as $comment) {
            if (preg_match('~@phpstan-ignore\b~', $comment->getText()) === 1) {
                $errors[] = RuleErrorBuilder::message('Do not ignore PHPStan errors with a comment: fix the code, or ignore the error in the PHPStan configuration.')
                    ->identifier('twigstan.ignoreComment')
                    ->line($comment->getStartLine())
                    ->build();
            }

            if ($isDeclaration || ! $comment instanceof Comment\Doc) {
                continue;
            }

            if (preg_match('~@(?:phpstan-)?var\b~', $comment->getText()) !== 1) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message('Do not override the type of a variable with an inline @var tag: type the code instead.')
                ->identifier('twigstan.inlineVar')
                ->line($comment->getStartLine())
                ->build();
        }

        return $errors;
    }
}
