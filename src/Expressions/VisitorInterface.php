<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

interface VisitorInterface
{
    public function visitBinary(BinaryExpression $expr): mixed;

    public function visitLiteral(LiteralExpression $expr): mixed;

    public function visitUnary(UnaryExpression $expr): mixed;
}
