<?php

declare(strict_types=1);

namespace TwigStan\PHPStan\DynamicReturnType;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\AutowiredService;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Twig\Extension\CoreExtension;

/**
 * `CoreExtension::toArray()` has no return type, which turns the context of the `with` tag into mixed.
 * Arrays are returned as they are, so keep the array shape.
 */
#[AutowiredService]
final readonly class ToArrayReturnType implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return CoreExtension::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'toArray';
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): ?Type {
        $args = $methodCall->getArgs();

        // Only the default `$preserveKeys = true` leaves arrays untouched.
        if (count($args) !== 1) {
            return null;
        }

        $argumentType = $scope->getType($args[0]->value);

        if ( ! $argumentType->isArray()->yes()) {
            return null;
        }

        return TypeCombinator::intersect($argumentType, $argumentType->toArray());
    }
}
