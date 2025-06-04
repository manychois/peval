<?php

declare(strict_types=1);

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\BinaryExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the BinaryExpression class.
 */
class BinaryExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        
        $operatorToken = new Token(TokenType::PLUS, '+', 0, 1, 1);
        
        $expr = new BinaryExpression($leftMock, $operatorToken, $rightMock);
        
        $this->assertSame($leftMock, $expr->left);
        $this->assertSame($operatorToken, $expr->operator);
        $this->assertSame($rightMock, $expr->right);
    }

    public function testAccept(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        
        $operatorToken = new Token(TokenType::MULTIPLY, '*', 5, 1, 6);
        $expr = new BinaryExpression($leftMock, $operatorToken, $rightMock);

        /** @var VisitorInterface&MockObject $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitBinary')
            ->with($this->identicalTo($expr))
            ->willReturn('test_result');

        $result = $expr->accept($visitorMock);
        $this->assertSame('test_result', $result);
    }

    public function testWithArithmeticOperators(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        
        // Test addition
        $plusToken = new Token(TokenType::PLUS, '+', 0, 1, 1);
        $expr = new BinaryExpression($leftMock, $plusToken, $rightMock);
        $this->assertSame(TokenType::PLUS, $expr->operator->type);
        
        // Test multiplication
        $multiplyToken = new Token(TokenType::MULTIPLY, '*', 0, 1, 1);
        $expr2 = new BinaryExpression($leftMock, $multiplyToken, $rightMock);
        $this->assertSame(TokenType::MULTIPLY, $expr2->operator->type);
    }

    public function testWithComparisonOperators(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        
        // Test less than
        $lessToken = new Token(TokenType::LESS, '<', 0, 1, 1);
        $expr = new BinaryExpression($leftMock, $lessToken, $rightMock);
        $this->assertSame(TokenType::LESS, $expr->operator->type);
        
        // Test equality
        $equalToken = new Token(TokenType::EQUAL, '==', 0, 1, 1);
        $expr2 = new BinaryExpression($leftMock, $equalToken, $rightMock);
        $this->assertSame(TokenType::EQUAL, $expr2->operator->type);
    }

    public function testWithLogicalOperators(): void
    {
        /** @var ExpressionInterface&MockObject $leftMock */
        $leftMock = $this->createMock(ExpressionInterface::class);
        
        /** @var ExpressionInterface&MockObject $rightMock */
        $rightMock = $this->createMock(ExpressionInterface::class);
        
        // Test logical AND
        $andToken = new Token(TokenType::SYMBOL_AND, '&&', 0, 1, 1);
        $expr = new BinaryExpression($leftMock, $andToken, $rightMock);
        $this->assertSame(TokenType::SYMBOL_AND, $expr->operator->type);
        
        // Test logical OR
        $orToken = new Token(TokenType::SYMBOL_OR, '||', 0, 1, 1);
        $expr2 = new BinaryExpression($leftMock, $orToken, $rightMock);
        $this->assertSame(TokenType::SYMBOL_OR, $expr2->operator->type);
    }
}
