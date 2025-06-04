<?php

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use PHPUnit\Framework\MockObject\MockObject;

class BinaryExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        $operatorToken = new Token(TokenType::Plus, '+', 1, 1);

        $exp = new BinaryExpression($leftMock, $operatorToken, $rightMock);
        $this->assertSame($leftMock, $exp->left);
        $this->assertSame($operatorToken, $exp->operator);
        $this->assertSame($rightMock, $exp->right);
    }

    public function testAccept(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        $operatorToken = new Token(TokenType::Minus, '-', 1, 1);

        $exp = new BinaryExpression($leftMock, $operatorToken, $rightMock);

        /** @var VisitorInterface&MockObject $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitBinary')
            ->with($this->identicalTo($exp));

        $exp->accept($visitorMock);
    }
}
