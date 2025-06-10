<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use Manychois\PevalTests\AbstractBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the UnaryExpression class.
 *
 * @internal
 *
 * @coversNothing
 */
class UnaryExpressionTest extends AbstractBaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);
        $operatorToken = new Token(TokenType::MINUS, '-', 0, 1, 1);

        $expr = new UnaryExpression($operatorToken, $expressionMock);
        $this->assertSame($operatorToken, $expr->operator);
        $this->assertSame($expressionMock, $expr->operand);
    }

    public function testAccept(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);
        $operatorToken = new Token(TokenType::PLUS, '+', 5, 1, 6);

        $expr = new UnaryExpression($operatorToken, $expressionMock);

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitUnary')
            ->with($this->identicalTo($expr))
            ->willReturn('unary_result')
        ;

        $result = $expr->accept($visitorMock);
        $this->assertSame('unary_result', $result);
    }

    public function testWithUnaryMinus(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);

        $minusToken = new Token(TokenType::MINUS, '-', 0, 1, 1);
        $expr = new UnaryExpression($minusToken, $expressionMock);

        $this->assertSame(TokenType::MINUS, $expr->operator->type);
        $this->assertSame('-', $expr->operator->text);
        $this->assertSame($expressionMock, $expr->operand);
    }

    public function testWithUnaryPlus(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);

        $plusToken = new Token(TokenType::PLUS, '+', 0, 1, 1);
        $expr = new UnaryExpression($plusToken, $expressionMock);

        $this->assertSame(TokenType::PLUS, $expr->operator->type);
        $this->assertSame('+', $expr->operator->text);
        $this->assertSame($expressionMock, $expr->operand);
    }

    public function testWithLogicalNot(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);

        $notToken = new Token(TokenType::NOT, '!', 0, 1, 1);
        $expr = new UnaryExpression($notToken, $expressionMock);

        $this->assertSame(TokenType::NOT, $expr->operator->type);
        $this->assertSame('!', $expr->operator->text);
        $this->assertSame($expressionMock, $expr->operand);
    }
}
