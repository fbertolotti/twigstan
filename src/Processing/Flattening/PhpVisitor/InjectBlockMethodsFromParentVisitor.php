<?php

declare(strict_types=1);

namespace TwigStan\Processing\Flattening\PhpVisitor;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use TwigStan\Twig\SourceLocation;

final class InjectBlockMethodsFromParentVisitor extends NodeVisitorAbstract
{
    /**
     * @param array<string, array{Stmt\ClassMethod, SourceLocation}> $blocks
     */
    public function __construct(
        private array $blocks,
    ) {}

    private int $classes = 0;

    public function enterNode(Node $node): null
    {
        if ($node instanceof Stmt\Class_) {
            $this->classes++;
        }

        return null;
    }

    public function leaveNode(Node $node): ?Stmt\Class_
    {
        // Embedded templates are compiled to more classes after the class of the template, and they have blocks of their own.
        if ( ! $node instanceof Stmt\Class_ || $this->classes !== 1) {
            return null;
        }

        foreach ($this->blocks as $blockName => [$method,$sourceLocation]) {
            $method->name = new Node\Identifier($blockName);

            if ($method->stmts === null) {
                continue;
            }

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new AppendSourceLocationVisitor($sourceLocation));

            $stmts = [];
            foreach ($traverser->traverse($method->stmts) as $stmt) {
                if ( ! $stmt instanceof Stmt) {
                    throw new LogicException('Expected the traversed method statements to remain statements.');
                }

                $stmts[] = $stmt;
            }

            $method->stmts = $stmts;

            $node->stmts[] = $method;
        }

        return $node;
    }
}
