<?php

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\LiteralExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use PHPUnit\Framework\MockObject\MockObject;

class LiteralExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        $token = new Token(TokenType::Number, '123', 1, 1);
        $exp = new LiteralExpression($token);
        $this->assertSame($token, $exp->value);
    }

    public function testAccept(): void
    {
        $token = new Token(TokenType::String, 'hello', 1, 1);
        $exp = new LiteralExpression($token);

        /** @var VisitorInterface&MockObject $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitLiteral')
            ->with($this->identicalTo($exp));

        $exp->accept($visitorMock);
    }
}
