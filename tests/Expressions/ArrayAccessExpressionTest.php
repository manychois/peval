<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\ArrayAccessExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\PevalTests\AbstractBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class ArrayAccessExpressionTest extends AbstractBaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $targetMock */
        $targetMock = $this->createMock(ExpressionInterface::class);

        /** @var ExpressionInterface&MockObject $offsetMock */
        $offsetMock = $this->createMock(ExpressionInterface::class);

        $exp = new ArrayAccessExpression($targetMock, $offsetMock);
        $this->assertSame($targetMock, $exp->target);
        $this->assertSame($offsetMock, $exp->offset);
    }

    public function testAccept(): void
    {
        /** @var ExpressionInterface&MockObject $targetMock */
        $targetMock = $this->createMock(ExpressionInterface::class);

        /** @var ExpressionInterface&MockObject $offsetMock */
        $offsetMock = $this->createMock(ExpressionInterface::class);

        $exp = new ArrayAccessExpression($targetMock, $offsetMock);

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitArrayAccess')
            ->with($this->identicalTo($exp))
        ;

        $exp->accept($visitorMock);
    }
}
