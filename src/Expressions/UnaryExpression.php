<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

use Manychois\Peval\Tokenisation\Token;

class UnaryExpression implements ExpressionInterface
{
    public function __construct(
        public readonly Token $operator,
        public readonly ExpressionInterface $expression
    ) {}

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitUnary($this);
    }
}
