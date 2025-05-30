<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

use Manychois\Peval\Token;

class LiteralExpression implements ExpressionInterface
{
    public function __construct(public readonly Token $value) {}

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitLiteral($this);
    }
}
