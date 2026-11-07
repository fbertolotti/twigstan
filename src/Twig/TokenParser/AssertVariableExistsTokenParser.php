<?php

declare(strict_types=1);

namespace TwigStan\Twig\TokenParser;

use PHPStan\DependencyInjection\AutowiredService;
use Twig\Error\SyntaxError;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

#[AutowiredService]
final class AssertVariableExistsTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): AssertVariableExistsNode
    {
        $stream = $this->parser->getStream();

        $name = $this->parser->parseExpression();

        $certainty = $stream->expect(Token::NAME_TYPE, ['no', 'maybe', 'yes'])->getValue();

        if ( ! is_string($certainty)) {
            throw new SyntaxError('The certainty must be a string.', $token->getLine(), $stream->getSourceContext());
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new AssertVariableExistsNode($name, $certainty, $token->getLine());
    }

    public function getTag(): string
    {
        return 'assert_variable_exists';
    }
}
