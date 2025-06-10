<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\VariableExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use Manychois\PevalTests\AbstractBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the VariableExpression class.
 *
 * @internal
 *
 * @coversNothing
 */
class VariableExpressionTest extends AbstractBaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        $token = new Token(TokenType::VARIABLE, '$x', 0, 1, 1);
        $expr = new VariableExpression($token);
        $this->assertSame($token, $expr->name);
    }

    public function testAccept(): void
    {
        $token = new Token(TokenType::VARIABLE, '$y', 5, 1, 6);
        $expr = new VariableExpression($token);

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitVariable')
            ->with($this->identicalTo($expr))
            ->willReturn('variable_result')
        ;

        $result = $expr->accept($visitorMock);
        $this->assertSame('variable_result', $result);
    }

    public function testWithSimpleVariables(): void
    {
        // Test simple variable
        $token1 = new Token(TokenType::VARIABLE, '$x', 0, 1, 1);
        $expr1 = new VariableExpression($token1);
        $this->assertSame(TokenType::VARIABLE, $expr1->name->type);
        $this->assertSame('$x', $expr1->name->text);

        // Test camelCase variable
        $token2 = new Token(TokenType::VARIABLE, '$myVariable', 0, 1, 1);
        $expr2 = new VariableExpression($token2);
        $this->assertSame(TokenType::VARIABLE, $expr2->name->type);
        $this->assertSame('$myVariable', $expr2->name->text);
    }

    public function testWithComplexVariables(): void
    {
        // Test underscore variable
        $token1 = new Token(TokenType::VARIABLE, '$my_variable', 0, 1, 1);
        $expr1 = new VariableExpression($token1);
        $this->assertSame(TokenType::VARIABLE, $expr1->name->type);
        $this->assertSame('$my_variable', $expr1->name->text);

        // Test variable with numbers
        $token2 = new Token(TokenType::VARIABLE, '$var123', 0, 1, 1);
        $expr2 = new VariableExpression($token2);
        $this->assertSame(TokenType::VARIABLE, $expr2->name->type);
        $this->assertSame('$var123', $expr2->name->text);
    }

    public function testTokenPositionProperties(): void
    {
        $token = new Token(TokenType::VARIABLE, '$test', 10, 2, 5);
        $expr = new VariableExpression($token);

        $this->assertSame(10, $expr->name->position);
        $this->assertSame(2, $expr->name->line);
        $this->assertSame(5, $expr->name->column);
    }
}
