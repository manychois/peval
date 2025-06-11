<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class StringInterpolationExpression implements ExpressionInterface
{
    /**
     * @var array<ExpressionInterface>
     */
    private array $expressions = [];

    public function addInnerExpression(ExpressionInterface $expr): void
    {
        $this->expressions[] = $expr;
    }

    /**
     * @return array<ExpressionInterface>
     */
    public function innerExpressions(): array
    {
        return $this->expressions;
    }

    // region implements ExpressionInterface

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitStringInterpolation($this);
    }

    // endregion implements ExpressionInterface
}
