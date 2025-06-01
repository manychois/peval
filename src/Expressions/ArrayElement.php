<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

final class ArrayElement
{
    public function __construct(
        public readonly ExpressionInterface $value,
        public readonly ?ExpressionInterface $key = null
    ) {}
}
