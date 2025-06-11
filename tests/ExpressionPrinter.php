<?php

declare(strict_types=1);

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\ArrayAccessExpression;
use Manychois\Peval\Expressions\ArrayExpression;
use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\FunctionCallExpression;
use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\MethodCallExpression;
use Manychois\Peval\Expressions\PropertyAccessExpression;
use Manychois\Peval\Expressions\StringInterpolationExpression;
use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Expressions\VariableExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;

class ExpressionPrinter implements VisitorInterface
{
    public function print(?ExpressionInterface $expression): string
    {
        $json = $expression?->accept($this);

        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    // region implements VisitorInterface

    public function visitArray(ArrayExpression $expr): mixed
    {
        $items = [];
        foreach ($expr->elements as $element) {
            $items[] = [
                'key' => $element->key?->accept($this),
                'value' => $element->value?->accept($this),
            ];
        }

        return [
            'type' => 'Array',
            'items' => $items,
        ];
    }

    public function visitArrayAccess(ArrayAccessExpression $expr): mixed
    {
        return [
            'type' => 'ArrayAccess',
            'target' => $expr->target?->accept($this),
            'offset' => $expr->offset?->accept($this),
        ];
    }

    public function visitBinary(BinaryExpression $expr): mixed
    {
        return [
            'type' => 'Binary',
            'left' => $expr->left?->accept($this),
            'operator' => $this->printToken($expr->operator),
            'right' => $expr->right?->accept($this),
        ];
    }

    public function visitFunctionCall(FunctionCallExpression $expr): mixed
    {
        $args = array_map(fn (ExpressionInterface $arg) => $arg->accept($this), $expr->arguments);

        return [
            'type' => 'FunctionCall',
            'name' => $expr->name->accept($this),
            'arguments' => $args,
        ];
    }

    public function visitLiteral(LiteralExpression $expr): mixed
    {
        return [
            'type' => 'Literal',
            'value' => $this->printToken($expr->value),
        ];
    }

    public function visitMethodCall(MethodCallExpression $expr): mixed
    {
        $args = array_map(fn (ExpressionInterface $arg) => $arg->accept($this), $expr->arguments);

        return [
            'type' => 'MethodCall',
            'target' => $expr->target->accept($this),
            'methodName' => $expr->methodName->accept($this),
            'arguments' => $args,
            'isStatic' => $expr->isStatic,
        ];
    }

    public function visitPropertyAccess(PropertyAccessExpression $expr): mixed
    {
        return [
            'type' => 'PropertyAccess',
            'target' => $expr->target->accept($this),
            'propertyName' => $expr->propertyName->accept($this),
            'isStatic' => $expr->isStatic,
        ];
    }

    public function visitStringInterpolation(StringInterpolationExpression $expr): mixed
    {
        $inners = array_map(fn (ExpressionInterface $inner) => $inner->accept($this), $expr->innerExpressions());

        return [
            'type' => 'StringInterpolation',
            'parts' => $inners,
        ];
    }

    public function visitUnary(UnaryExpression $expr): mixed
    {
        return [
            'type' => 'Unary',
            'operator' => $this->printToken($expr->operator),
            'operand' => $expr->operand?->accept($this),
        ];
    }

    public function visitVariable(VariableExpression $expr): mixed
    {
        return [
            'type' => 'Variable',
            'name' => $this->printToken($expr->name),
        ];
    }

    // endregion implements VisitorInterface

    private function printToken(Token $token): string
    {
        return sprintf(
            '%s(%s) at (%d, %d)',
            $token->type->name,
            $token->text,
            $token->line,
            $token->column
        );
    }
}
