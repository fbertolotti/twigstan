<?php

declare(strict_types=1);

namespace TwigStan\Twig\TokenParser;

use PHPStan\DependencyInjection\AutowiredService;
use Twig\Error\SyntaxError;
use Twig\Node\EmptyNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

#[AutowiredService]
final class AssertTypeTokenParser extends AbstractTokenParser
{
    /**
     * @param bool $compile Whether or not to compile the PHPStan assertType call.
     */
    public function __construct(
        private readonly bool $compile = true,
    ) {}

    public function parse(Token $token): AssertTypeNode | EmptyNode
    {
        $stream = $this->parser->getStream();

        $name = $this->parser->parseExpression();

        $expectedType = $stream->expect(Token::STRING_TYPE)->getValue();

        if ( ! is_string($expectedType)) {
            throw new SyntaxError('The expected type must be a string.', $token->getLine(), $stream->getSourceContext());
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        if ( ! $this->compile) {
            return new EmptyNode($token->getLine());
        }

        return new AssertTypeNode($name, $expectedType, $token->getLine());
    }

    public function getTag(): string
    {
        return 'assert_type';
    }
}
