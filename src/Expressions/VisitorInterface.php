<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

interface VisitorInterface
{
    public function visitLiteral(LiteralExpression $expr): mixed;
}
