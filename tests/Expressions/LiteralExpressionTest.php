<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use Manychois\PevalTests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the LiteralExpression class.
 *
 * @internal
 *
 * @coversNothing
 */
class LiteralExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        $token = new Token(TokenType::INTEGER, '123', 0, 1, 1);
        $expr = new LiteralExpression($token);
        $this->assertSame($token, $expr->value);
    }

    public function testAccept(): void
    {
        $token = new Token(TokenType::STRING, 'hello', 5, 1, 6);
        $expr = new LiteralExpression($token);

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitLiteral')
            ->with($this->identicalTo($expr))
            ->willReturn('literal_result')
        ;

        $result = $expr->accept($visitorMock);
        $this->assertSame('literal_result', $result);
    }

    public function testWithBooleanLiterals(): void
    {
        // Test true boolean
        $trueToken = new Token(TokenType::BOOL, 'true', 0, 1, 1);
        $expr = new LiteralExpression($trueToken);
        $this->assertSame(TokenType::BOOL, $expr->value->type);
        $this->assertSame('true', $expr->value->text);

        // Test false boolean
        $falseToken = new Token(TokenType::BOOL, 'false', 0, 1, 1);
        $expr2 = new LiteralExpression($falseToken);
        $this->assertSame(TokenType::BOOL, $expr2->value->type);
        $this->assertSame('false', $expr2->value->text);
    }

    public function testWithNumericLiterals(): void
    {
        // Test integer
        $intToken = new Token(TokenType::INTEGER, '42', 0, 1, 1);
        $expr = new LiteralExpression($intToken);
        $this->assertSame(TokenType::INTEGER, $expr->value->type);
        $this->assertSame('42', $expr->value->text);

        // Test float
        $floatToken = new Token(TokenType::FLOAT, '3.14', 0, 1, 1);
        $expr2 = new LiteralExpression($floatToken);
        $this->assertSame(TokenType::FLOAT, $expr2->value->type);
        $this->assertSame('3.14', $expr2->value->text);
    }

    public function testWithStringLiterals(): void
    {
        // Test string
        $stringToken = new Token(TokenType::STRING, 'hello world', 0, 1, 1);
        $expr = new LiteralExpression($stringToken);
        $this->assertSame(TokenType::STRING, $expr->value->type);
        $this->assertSame('hello world', $expr->value->text);

        // Test null
        $nullToken = new Token(TokenType::NULL, 'null', 0, 1, 1);
        $expr2 = new LiteralExpression($nullToken);
        $this->assertSame(TokenType::NULL, $expr2->value->type);
        $this->assertSame('null', $expr2->value->text);
    }
}
