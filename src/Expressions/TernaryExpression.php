<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class TernaryExpression implements ExpressionInterface
{
    public function __construct(
        public readonly ExpressionInterface $condition,
        public readonly ExpressionInterface $trueExpr,
        public readonly ExpressionInterface $falseExpr,
    ) {
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitTernary($this);
    }
}
