<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\StringInterpolationExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use Manychois\PevalTests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the StringInterpolationExpression class.
 *
 * @internal
 *
 * @coversNothing
 */
class StringInterpolationExpressionTest extends BaseTestCase
{
    public function testAddAndGetInnerExpressions(): void
    {
        $exp = new StringInterpolationExpression();
        $this->assertEmpty($exp->innerExpressions());

        /** @var ExpressionInterface&MockObject $mock1 */
        $mock1 = $this->createMock(ExpressionInterface::class);
        $exp->addInnerExpression($mock1);
        $this->assertSame([$mock1], $exp->innerExpressions());

        /** @var ExpressionInterface&MockObject $mock2 */
        $mock2 = $this->createMock(ExpressionInterface::class);
        $exp->addInnerExpression($mock2);
        $this->assertSame([$mock1, $mock2], $exp->innerExpressions());
    }

    public function testAccept(): void
    {
        $exp = new StringInterpolationExpression();

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitStringInterpolation')
            ->with($this->identicalTo($exp))
        ;

        $exp->accept($visitorMock);
    }
}
