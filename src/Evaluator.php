<?php

declare(strict_types=1);

namespace Manychois\Peval;

use LogicException;
use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\StringInterpolationExpression;
use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Expressions\VariableExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;

class Evaluator implements VisitorInterface
{
    /**
     * @var array<string,mixed>
     */
    private array $context;

    /**
     * Creates a new Evaluator instance.
     *
     * @param array<string,mixed> $context The context in which to evaluate expressions.
     *                                     This can include variables and their values that the evaluator can access.
     */
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

        $numeric = function (mixed $value, string $leftOrRight) use ($expr): int|float|string {
            if (is_numeric($value)) {
                return $value;
            }
            $message = sprintf(
                'Invalid %s operand for mathematical operator at line %d, column %d, found %s.',
                $leftOrRight,
                $expr->operator->line,
                $expr->operator->column,
                \get_debug_type($value)
            );

            throw new LogicException($message);
        };

        $str = function (mixed $value, string $leftOrRight) use ($expr): string {
            if (is_string($value)) {
                return $value;
            }
            if (null === $value || is_scalar($value) || is_object($value) && method_exists($value, '__toString')) {
                return (string) $value;
            }
            $message = sprintf(
                'Invalid %s operand for concatenation operator at line %d, column %d, found %s.',
                $leftOrRight,
                $expr->operator->line,
                $expr->operator->column,
                \get_debug_type($value)
            );

            throw new LogicException($message);
        };

        return match ($opType) {
            TokenType::PLUS => $numeric($left, 'left') + $numeric($right, 'right'),
            TokenType::MINUS => $numeric($left, 'left') - $numeric($right, 'right'),
            TokenType::MULTIPLY => $numeric($left, 'left') * $numeric($right, 'right'),
            TokenType::DIVIDE => $numeric($left, 'left') / $numeric($right, 'right'),
            TokenType::MODULO => $numeric($left, 'left') % $numeric($right, 'right'),
            TokenType::POWER => $numeric($left, 'left') ** $numeric($right, 'right'),
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
            TokenType::DOT => $str($left, 'left') . $str($right, 'right'),
            default => throw new LogicException(sprintf('Unsupported binary operator: %s', $expr->operator->text)),
        };
    }

    public function visitLiteral(LiteralExpression $expr): mixed
    {
        return match ($expr->value->type) {
            TokenType::INTEGER => intval($expr->value->text),
            TokenType::FLOAT => floatval($expr->value->text),
            TokenType::BOOL => filter_var($expr->value->text, FILTER_VALIDATE_BOOLEAN),
            TokenType::NULL => null,
            TokenType::STRING => $this->evaluateLiteralString($expr->value),
            default => throw new LogicException(sprintf('Unsupported literal type: %s', $expr->value->type->name)),
        };
    }

    public function visitStringInterpolation(StringInterpolationExpression $expr): mixed
    {
        $result = '';
        foreach ($expr->innerExpressions() as $inner) {
            if ($inner instanceof LiteralExpression) {
                if (TokenType::STRING === $inner->value->type) {
                    $value = $this->evaluateDoubleQuoteString($inner->value->text);
                } else {
                    $value = $this->evaluate($inner);
                }
            } else {
                $value = $this->evaluate($inner);
            }

            if (!is_string($value)) {
                if (null === $value || is_scalar($value) || is_object($value) && method_exists($value, '__toString')) {
                    $value = (string) $value;
                } else {
                    throw new LogicException(sprintf('Invalid value in interpolation string, found %s.', get_debug_type($value)));
                }
            }
            $result .= $value;
        }

        return $result;
    }

    public function visitUnary(UnaryExpression $expr): mixed
    {
        $value = $this->evaluate($expr->expression);

        $numeric = function (mixed $value) use ($expr): int|float|string {
            if (is_numeric($value)) {
                return $value;
            }
            $message = sprintf(
                'Invalid operand for unary operator at line %d, column %d, found %s.',
                $expr->operator->line,
                $expr->operator->column,
                get_debug_type($value)
            );

            throw new LogicException($message);
        };

        return match ($expr->operator->type) {
            TokenType::MINUS => -$numeric($value),
            TokenType::NOT => !$value,
            TokenType::PLUS => +$numeric($value),
            default => throw new LogicException(sprintf('Unsupported unary operator: %s', $expr->operator->text)),
        };
    }

    public function visitVariable(VariableExpression $expr): mixed
    {
        $name = substr($expr->name->text, 1); // Remove the leading '$'
        if (!array_key_exists($name, $this->context)) {
            throw new LogicException(sprintf('Undefined variable: %s', $expr->name->text));
        }

        return $this->context[$name];
    }

    // endregion implements VisitorInterface

    private function evaluateLiteralString(Token $token): string
    {
        assert(TokenType::STRING === $token->type);
        $quote = $token->text[0];
        $text = substr($token->text, 1, strlen($token->text) - 2);
        if ('\'' === $quote) {
            $text = str_replace(['\\\\', '\\\''], ['\\', '\''], $text);
        } elseif ('"' === $quote) {
            $text = $this->evaluateDoubleQuoteString($text);
        }

        return $text;
    }

    private function evaluateDoubleQuoteString(string $content): string
    {
        $pattern = '/\\\([nrtvef\$"]|[0-7]{1,3}|x[0-9A-Fa-f]{1,2}|u{[0-9A-Fa-f]+})/';
        $callback = function (array $matches): string {
            assert(isset($matches[1]) && is_string($matches[1]));
            $ch0 = $matches[1][0];
            $value = match ($ch0) {
                'n' => "\n",
                'r' => "\r",
                't' => "\t",
                'v' => "\v",
                'e' => "\e",
                'f' => "\f",
                '\\', '$', '"' => $ch0,
                default => '',
            };
            if ('' !== $value) {
                return $value;
            }

            if ('x' === $ch0) {
                $value = chr((int) hexdec(substr($matches[1], 1)));
            } elseif ('u' === $ch0) {
                $value = mb_convert_encoding('&#x' . substr($matches[1], 1) . ';', 'UTF-8', 'HTML-ENTITIES');
                if (!is_string($value)) {
                    throw new LogicException(sprintf('Invalid Unicode escape sequence: %s', $matches[1]));
                }
            } else {
                $value = chr((int) octdec($matches[1]));
            }

            return $value;
        };

        $result = preg_replace_callback($pattern, $callback, $content);
        assert(is_string($result));

        return $result;
    }
}
