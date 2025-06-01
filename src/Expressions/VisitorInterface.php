<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

interface VisitorInterface
{
    public function visitArray(ArrayExpression $expr): mixed;

    public function visitArrayAccess(ArrayAccessExpression $expr): mixed;

    public function visitBinary(BinaryExpression $expr): mixed;

    public function visitLiteral(LiteralExpression $expr): mixed;

    public function visitStringInterpolation(StringInterpolationExpression $expr): mixed;

    public function visitUnary(UnaryExpression $expr): mixed;

    public function visitVariable(VariableExpression $expr): mixed;
}
