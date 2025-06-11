<?php

declare(strict_types=1);

namespace Manychois\Peval\Expressions;

class MethodCallExpression implements ExpressionInterface
{
    public readonly ExpressionInterface $target;
    public readonly ExpressionInterface $methodName;
    /**
     * @var array<ExpressionInterface>
     */
    public readonly array $arguments;
    public readonly bool $isStatic;

    /**
     * Constructor for a method call expression.
     *
     * @param ExpressionInterface        $target     the target object or class on which the method is called
     * @param ExpressionInterface        $methodName the name of the method being called
     * @param array<ExpressionInterface> $arguments  the arguments to the method
     * @param bool                       $isStatic   whether the method call is static or not
     */
    public function __construct(ExpressionInterface $target, ExpressionInterface $methodName, array $arguments, bool $isStatic)
    {
        $this->target = $target;
        $this->methodName = $methodName;
        $this->arguments = $arguments;
        $this->isStatic = $isStatic;
    }

    public function accept(VisitorInterface $visitor): mixed
    {
        return $visitor->visitMethodCall($this);
    }
}
