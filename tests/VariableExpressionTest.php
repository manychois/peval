<?php

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\VariableExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\Peval\Tokenisation\Token;
use Manychois\Peval\Tokenisation\TokenType;
use PHPUnit\Framework\MockObject\MockObject;

class VariableExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        $token = new Token(TokenType::Identifier, 'x', 1, 1);
        $exp = new VariableExpression($token);
        $this->assertSame($token, $exp->name);
    }

    public function testAccept(): void
    {
        $token = new Token(TokenType::Identifier, 'y', 1, 1);
        $exp = new VariableExpression($token);

        /** @var VisitorInterface&MockObject $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitVariable')
            ->with($this->identicalTo($exp));

        $exp->accept($visitorMock);
    }
}
