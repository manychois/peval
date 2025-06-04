<?php

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\UnaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use PHPUnit\Framework\MockObject\MockObject;

class UnaryExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);
        $operatorToken = new Token(TokenType::Minus, '-', 1, 1);

        $exp = new UnaryExpression($operatorToken, $expressionMock);
        $this->assertSame($operatorToken, $exp->operator);
        $this->assertSame($expressionMock, $exp->expression);
    }

    public function testAccept(): void
    {
        /** @var ExpressionInterface&MockObject $expressionMock */
        $expressionMock = $this->createMock(ExpressionInterface::class);
        $operatorToken = new Token(TokenType::Plus, '+', 1, 1);

        $exp = new UnaryExpression($operatorToken, $expressionMock);

        /** @var VisitorInterface&MockObject $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitUnary')
            ->with($this->identicalTo($exp));

        $exp->accept($visitorMock);
    }
}
