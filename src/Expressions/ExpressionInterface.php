<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

interface ExpressionInterface
{
    public function accept(VisitorInterface $visitor): mixed;
}
