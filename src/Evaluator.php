<?php

declare(strict_types=1);

namespace Manychois\Peval;

use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\VisitorInterface;

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

    public function visitLiteral(LiteralExpression $expr): mixed
    {
        if (TokenType::INTEGER === $expr->value->type) {
            return intval($expr->value->text);
        }

        throw new \InvalidArgumentException(
            'Unsupported literal type: '.$expr->value->type
        );
    }

    // endregion implements VisitorInterface
}
