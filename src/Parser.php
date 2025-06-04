<?php

declare(strict_types=1);

namespace Manychois\Peval;

use Manychois\Peval\Expressions\ArrayAccessExpression;
use Manychois\Peval\Expressions\ArrayElement;
use Manychois\Peval\Expressions\ArrayExpression;
use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\PropertyAccessExpression;
use Manychois\Peval\Expressions\StringInterpolationExpression;
use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Expressions\VariableExpression;
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
        return $this->parseWordOr($lp);
    }

    private function parseWordOr(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseXor($lp);
        if ($lp->matchAny(TokenType::WORD_OR)) {
            $operator = $lp->previous();
            $right = $this->parseXor($lp);

            return new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseXor(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseWordAnd($lp);
        if ($lp->matchAny(TokenType::XOR)) {
            $operator = $lp->previous();
            $right = $this->parseWordAnd($lp);

            return new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseWordAnd(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseSymbolOr($lp);
        if ($lp->matchAny(TokenType::WORD_AND)) {
            $operator = $lp->previous();
            $right = $this->parseSymbolOr($lp);

            return new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseSymbolOr(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseSymbolAnd($lp);
        if ($lp->matchAny(TokenType::SYMBOL_OR)) {
            $operator = $lp->previous();
            $right = $this->parseSymbolAnd($lp);

            return new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseSymbolAnd(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseEquality($lp);
        if ($lp->matchAny(TokenType::SYMBOL_AND)) {
            $operator = $lp->previous();
            $right = $this->parseEquality($lp);

            return new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseEquality(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseComparison($lp);
        while ($lp->matchAny(TokenType::EQUAL, TokenType::NOT_EQUAL, TokenType::IDENTICAL, TokenType::NOT_IDENTICAL)) {
            $operator = $lp->previous();
            $right = $this->parseComparison($lp);
            $expr = new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseComparison(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseConcatenation($lp);
        while ($lp->matchAny(TokenType::LESS, TokenType::LESS_EQUAL, TokenType::GREATER, TokenType::GREATER_EQUAL)) {
            $operator = $lp->previous();
            $right = $this->parseConcatenation($lp);
            $expr = new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
    }

    private function parseConcatenation(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parseAddition($lp);
        while ($lp->matchAny(TokenType::DOT)) {
            $operator = $lp->previous();
            $right = $this->parseAddition($lp);
            $expr = new BinaryExpression($expr, $operator, $right);
        }

        return $expr;
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

    private function parseUnary(TokenStream $lp): ExpressionInterface
    {
        if ($lp->matchAny(TokenType::MINUS, TokenType::NOT, TokenType::PLUS)) {
            $operator = $lp->previous();
            $right = $this->parseUnary($lp);

            return new UnaryExpression($operator, $right);
        }

        return $this->parsePostfix($lp);
    }

    private function parsePostfix(TokenStream $lp): ExpressionInterface
    {
        $expr = $this->parsePrimary($lp);

        if ($lp->matchAny(TokenType::DOUBLE_COLON)) {
            // Static property or method access
            if (!$lp->matchAny(TokenType::IDENTIFIER)) {
                throw $lp->createParseException('Expected identifier after "::"');
            }
            $prop = new LiteralExpression($lp->previous());
            $expr = new PropertyAccessExpression($expr, $prop, true);
        }

        while ($lp->matchAny(TokenType::LEFT_BRACKET)) {
            $offset = $this->parseExpression($lp);
            $expr = new ArrayAccessExpression($expr, $offset);
            if (!$lp->matchAny(TokenType::RIGHT_BRACKET)) {
                throw $lp->createParseException('Expected closing bracket');
            }
        }

        while ($lp->matchAny(TokenType::ARROW)) {
            if (!$lp->matchAny(TokenType::IDENTIFIER)) {
                throw $lp->createParseException('Expected identifier after "->"');
            }
            $prop = new LiteralExpression($lp->previous());
            $expr = new PropertyAccessExpression($expr, $prop, false);
        }

        return $expr;
    }

    private function parsePrimary(TokenStream $lp): ExpressionInterface
    {
        if ($lp->matchAny(TokenType::BOOL, TokenType::INTEGER, TokenType::FLOAT, TokenType::NULL, TokenType::STRING)) {
            return new LiteralExpression($lp->previous());
        }

        if ($lp->matchAny(TokenType::VARIABLE)) {
            return new VariableExpression($lp->previous());
        }

        if ($lp->matchAny(TokenType::LEFT_PARENTHESIS)) {
            $expr = $this->parseExpression($lp);
            if (!$lp->matchAny(TokenType::RIGHT_PARENTHESIS)) {
                throw $lp->createParseException('Expected closing parenthesis');
            }

            return $expr;
        }

        if ($lp->matchAny(TokenType::QUOTE)) {
            $expr = new StringInterpolationExpression();
            while (!$lp->matchAny(TokenType::QUOTE)) {
                if (TokenType::LEFT_BRACE === $lp->current()->type) {
                    $lp->advance(); // Consume the opening brace
                    $expr->addInnerExpression($this->parseExpression($lp));
                    if (!$lp->matchAny(TokenType::RIGHT_BRACE)) {
                        throw $lp->createParseException('Expected closing brace');
                    }
                } else {
                    $expr->addInnerExpression($this->parseExpression($lp));
                }
            }

            return $expr;
        }

        if ($lp->matchAny(TokenType::LEFT_BRACKET)) {
            $elements = [];
            while (!$lp->matchAny(TokenType::RIGHT_BRACKET)) {
                if (count($elements) > 0) {
                    if (!$lp->matchAny(TokenType::COMMA, TokenType::RIGHT_BRACKET)) {
                        throw $lp->createParseException('Expected comma or closing bracket');
                    }
                    if (TokenType::RIGHT_BRACKET === $lp->previous()->type) {
                        break;
                    }
                    if ($lp->matchAny(TokenType::RIGHT_BRACKET)) {
                        break;
                    }
                }

                $value = $this->parseExpression($lp);
                if ($lp->matchAny(TokenType::DOUBLE_ARROW)) {
                    $key = $value;
                    $value = $this->parseExpression($lp);
                    $elements[] = new ArrayElement($value, $key);
                } else {
                    $elements[] = new ArrayElement($value);
                }
            }

            return new ArrayExpression($elements);
        }

        if ($lp->matchAny(TokenType::ARRAY)) {
            if (!$lp->matchAny(TokenType::LEFT_PARENTHESIS)) {
                throw $lp->createParseException('Expected opening parenthesis after "array" keyword');
            }

            $elements = [];
            while (!$lp->matchAny(TokenType::RIGHT_PARENTHESIS)) {
                if (count($elements) > 0) {
                    if (!$lp->matchAny(TokenType::COMMA, TokenType::RIGHT_PARENTHESIS)) {
                        throw $lp->createParseException('Expected comma or closing parenthesis');
                    }
                    if (TokenType::RIGHT_PARENTHESIS === $lp->previous()->type) {
                        break;
                    }
                    if ($lp->matchAny(TokenType::RIGHT_PARENTHESIS)) {
                        break;
                    }
                }

                $value = $this->parseExpression($lp);
                if ($lp->matchAny(TokenType::DOUBLE_ARROW)) {
                    $key = $value;
                    $value = $this->parseExpression($lp);
                    $elements[] = new ArrayElement($value, $key);
                } else {
                    $elements[] = new ArrayElement($value);
                }
            }

            return new ArrayExpression($elements);
        }

        if ($lp->matchAny(TokenType::IDENTIFIER)) {
            $identifier = new LiteralExpression($lp->previous());
            if ($lp->matchAny(TokenType::DOUBLE_COLON)) {
                // Static property or method access
                if (!$lp->matchAny(TokenType::IDENTIFIER)) {
                    throw $lp->createParseException('Expected identifier after "::"');
                }
                $prop = new LiteralExpression($lp->previous());

                return new PropertyAccessExpression($identifier, $prop, true);
            }

            // TODO
        }

        throw $lp->createParseException();
    }
}
