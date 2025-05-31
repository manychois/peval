<?php

declare(strict_types=1);

namespace Manychois\Peval;

use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Tokenisation\TokenStream;
use Manychois\Peval\Tokenisation\TokenType;

class Parser
{
    public function parse(string $source): ExpressionInterface
    {
        $lp = new TokenStream($source);

        return $this->parseExpression($lp);
    }

    private function parseExpression(TokenStream $lp): ExpressionInterface
    {
        return $this->parseAddition($lp);
    }

    private function parseAddition(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseMultiplication($lp);
        while ($lp->matchAny(TokenType::PLUS, TokenType::MINUS)) {
            $operator = $lp->previous();
            $right = $this->parseMultiplication($lp);
            $expr = new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseExponentiation(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseUnary($lp);
        // Right-associative: parse from right to left
        if ($lp->matchAny(TokenType::POWER)) {
            $operator = $lp->previous();
            $right = $this->parseExponentiation($lp); // Recursive call for right-associativity

            return new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseMultiplication(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseExponentiation($lp);
        while ($lp->matchAny(TokenType::MULTIPLY, TokenType::DIVIDE, TokenType::MODULO)) {
            $operator = $lp->previous();
            $right = $this->parseExponentiation($lp);
            $expr = new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parsePrimary(TokenStream $lp): ExpressionInterface
    {
        if ($lp->matchAny(TokenType::BOOL, TokenType::INTEGER, TokenType::FLOAT)) {
            return new LiteralExpression($lp->previous());
        }

        if ($lp->matchAny(TokenType::LEFT_PARENTHESIS)) {
            $expr = $this->parseExpression($lp);
            if (!$lp->matchAny(TokenType::RIGHT_PARENTHESIS)) {
                throw $lp->createParseError('Expected closing parenthesis');
            }

            return $expr;
        }

        throw $lp->createParseError();
    }

    private function parseUnary(TokenStream $lp): ExpressionInterface
    {
        if ($lp->matchAny(TokenType::MINUS, TokenType::NOT, TokenType::PLUS)) {
            $operator = $lp->previous();
            $expression = $this->parsePrimary($lp);

            return new UnaryExpression($operator, $expression);
        }

        return $this->parsePrimary($lp);
    }
}
