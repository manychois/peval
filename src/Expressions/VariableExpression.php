<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

use Manychois\Peval\Tokenisation\Token;

class VariableExpression implements ExpressionInterface
{
    public function __construct(
        public readonly Token $name,
    ) {
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitVariable($this);
    }
}
