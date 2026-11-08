<?php

declare(strict_types=1);

namespace TwigStan\Processing\Compilation\PhpVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * The `with` tag checks at runtime that its variables are iterable. PHPStan reports that check as always true.
 *
 * Find:    if ( ! is_iterable($variables)) { throw new RuntimeError('Variables passed to the "with" tag must be a mapping.'); }
 * Remove:  the whole `if` statement.
 */
final class RemoveWithMappingGuardVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node): ?int
    {
        if ( ! $node instanceof Stmt\If_ || $node->elseifs !== [] || $node->else !== null) {
            return null;
        }

        if ( ! $node->cond instanceof Expr\BooleanNot) {
            return null;
        }

        $call = $node->cond->expr;

        if ( ! $call instanceof Expr\FuncCall || ! $call->name instanceof Node\Name || $call->name->toLowerString() !== 'is_iterable') {
            return null;
        }

        if (count($node->stmts) !== 1 || ! $node->stmts[0] instanceof Stmt\Expression || ! $node->stmts[0]->expr instanceof Expr\Throw_) {
            return null;
        }

        $new = $node->stmts[0]->expr->expr;

        if ( ! $new instanceof Expr\New_) {
            return null;
        }

        $message = $new->args[0]->value ?? null;

        if ( ! $message instanceof Node\Scalar\String_ || ! str_contains($message->value, 'must be a mapping')) {
            return null;
        }

        return NodeVisitor::REMOVE_NODE;
    }
}
