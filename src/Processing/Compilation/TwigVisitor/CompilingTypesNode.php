<?php

declare(strict_types=1);

namespace TwigStan\Processing\Compilation\TwigVisitor;

use InvalidArgumentException;
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\TypesNode;

#[YieldReady]
final class CompilingTypesNode extends TypesNode
{
    /**
     * @return array<string, array{type: string, optional: bool}>
     */
    public static function getMapping(Node $node): array
    {
        $attribute = $node->getAttribute('mapping');

        if ( ! is_array($attribute)) {
            throw new InvalidArgumentException('The "mapping" attribute must be an array.');
        }

        $mapping = [];
        foreach ($attribute as $name => $entry) {
            if ( ! is_string($name) || ! is_array($entry) || ! is_string($entry['type'] ?? null) || ! is_bool($entry['optional'] ?? null)) {
                throw new InvalidArgumentException('The "mapping" attribute must map names to a type and an optional flag.');
            }

            $mapping[$name] = ['type' => $entry['type'], 'optional' => $entry['optional']];
        }

        return $mapping;
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        foreach (self::getMapping($this) as $name => ['type' => $type, 'optional' => $optional]) {
            $compiler->write('twigstan_type_hint($context, ')
                ->repr($name)
                ->raw(', ')
                ->repr($type)
                ->raw(', ')
                ->repr($optional)
                ->raw(");\n");
        }
    }
}
