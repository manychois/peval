<?php

declare(strict_types=1);

namespace Manychois\Peval;

use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Expressions\VariableExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\TokenType;

class Evaluator implements VisitorInterface
{
    private array $context;

    public function __construct(array $context = [])
    {
        $this->context = $context;
    }

    public function evaluate(ExpressionInterface $expression): mixed
    {
        return $expression->accept($this);
    }

    // region implements VisitorInterface

    public function visitBinary(BinaryExpression $expr): mixed
    {
        $left = $this->evaluate($expr->left);
        $opType = $expr->operator->type;

        // Short-circuit evaluation for logical operators
        if (TokenType::SYMBOL_AND === $opType || TokenType::WORD_AND === $opType) {
            if (!$left) {
                return false;
            }
        } elseif (TokenType::SYMBOL_OR === $opType || TokenType::WORD_OR === $opType) {
            if ($left) {
                return true;
            }
        }

        $right = $this->evaluate($expr->right);

        return match ($opType) {
            TokenType::PLUS => $left + $right,
            TokenType::MINUS => $left - $right,
            TokenType::MULTIPLY => $left * $right,
            TokenType::DIVIDE => $left / $right,
            TokenType::MODULO => $left % $right,
            TokenType::POWER => $left ** $right,
            TokenType::EQUAL => $left == $right,
            TokenType::NOT_EQUAL => $left != $right,
            TokenType::IDENTICAL => $left === $right,
            TokenType::NOT_IDENTICAL => $left !== $right,
            TokenType::LESS => $left < $right,
            TokenType::LESS_EQUAL => $left <= $right,
            TokenType::GREATER => $left > $right,
            TokenType::GREATER_EQUAL => $left >= $right,
            TokenType::SYMBOL_AND, TokenType::WORD_AND => $left && $right,
            TokenType::SYMBOL_OR, TokenType::WORD_OR => $left || $right,
            TokenType::XOR => $left xor $right,
            default => throw new \LogicException(sprintf('Unsupported binary operator: %s', $expr->operator->text)),
        };
    }

    public function visitLiteral(LiteralExpression $expr): mixed
    {
        return match ($expr->value->type) {
            TokenType::INTEGER => intval($expr->value->text),
            TokenType::FLOAT => floatval($expr->value->text),
            TokenType::BOOL => filter_var($expr->value->text, FILTER_VALIDATE_BOOLEAN),
            default => throw new \LogicException(sprintf('Unsupported literal type: %s', $expr->value->type)),
        };
    }

    public function visitUnary(UnaryExpression $expr): mixed
    {
        $value = $this->evaluate($expr->expression);

        return match ($expr->operator->type) {
            TokenType::MINUS => -$value,
            TokenType::NOT => !$value,
            TokenType::PLUS => +$value,
            default => throw new \LogicException(sprintf('Unsupported unary operator: %s', $expr->operator->text)),
        };
    }

    public function visitVariable(VariableExpression $expr): mixed
    {
        $name = substr($expr->name->text, 1); // Remove the leading '$'
        if (!array_key_exists($name, $this->context)) {
            throw new \LogicException(sprintf('Undefined variable: %s', $expr->name->text));
        }

        return $this->context[$name];
    }

    // endregion implements VisitorInterface
}
