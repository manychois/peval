<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\ArrayElement;
use Manychois\Peval\Expressions\ArrayExpression;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\PevalTests\AbstractBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class ArrayExpressionTest extends AbstractBaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $valueMock */
        $valueMock = $this->createMock(ExpressionInterface::class);
        $element = new ArrayElement($valueMock);
        $elements = [$element];

        $exp = new ArrayExpression($elements);
        $this->assertSame($elements, $exp->elements);
        $this->assertCount(1, $exp->elements);
        $this->assertSame($element, $exp->elements[0]);
    }

    public function testAccept(): void
    {
        $elements = [];
        $exp = new ArrayExpression($elements);

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitArray')
            ->with($this->identicalTo($exp))
        ;

        $exp->accept($visitorMock);
    }
}
