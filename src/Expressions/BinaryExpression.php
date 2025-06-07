<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

use Manychois\Peval\Tokenisation\Token;

class BinaryExpression implements ExpressionInterface
{
    public function __construct(
        public readonly ExpressionInterface $left,
        public readonly Token $operator,
        public readonly ExpressionInterface $right,
    ) {
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitBinary($this);
    }
}
