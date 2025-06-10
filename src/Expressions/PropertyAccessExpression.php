<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class PropertyAccessExpression implements ExpressionInterface
{
    public function __construct(
        public readonly ExpressionInterface $target,
        public readonly ExpressionInterface $propertyName,
        public readonly bool $isStatic,
    ) {
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitPropertyAccess($this);
    }
}
