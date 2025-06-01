<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class ArrayExpression implements ExpressionInterface
{
    /**
     * @var ArrayElement[]
     */
    public readonly array $elements;

    /**
     * @param ArrayElement[] $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitArray($this);
    }
}
