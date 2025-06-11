<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class ArrayAccessExpression implements ExpressionInterface
{
    public function __construct(
        public readonly ExpressionInterface $target,
        public readonly ExpressionInterface $offset,
    ) {
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitArrayAccess($this);
    }
}
