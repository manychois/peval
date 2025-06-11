<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class FunctionCallExpression implements ExpressionInterface
{
    public readonly ExpressionInterface $name;
    /**
     * @var array<ExpressionInterface>
     */
    public readonly array $arguments;

    /**
     * Constructor for a function call expression.
     *
     * @param ExpressionInterface        $name      the name of the function being called
     * @param array<ExpressionInterface> $arguments the arguments to the function
     */
    public function __construct(ExpressionInterface $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitFunctionCall($this);
    }
}
