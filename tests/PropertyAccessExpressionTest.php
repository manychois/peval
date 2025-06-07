<?php

declare(strict_types=1);

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\Peval\Expressions\PropertyAccessExpression;
use Manychois\Peval\Expressions\VisitorInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class PropertyAccessExpressionTest extends BaseTestCase
{
    public function testConstructorAndProperties(): void
    {
        /** @var ExpressionInterface&MockObject $targetMock */
        $targetMock = $this->createMock(ExpressionInterface::class);

        /** @var ExpressionInterface&MockObject $propertyNameMock */
        $propertyNameMock = $this->createMock(ExpressionInterface::class);

        // Test with isStatic = false
        $expInstance = new PropertyAccessExpression($targetMock, $propertyNameMock, false);
        $this->assertSame($targetMock, $expInstance->target);
        $this->assertSame($propertyNameMock, $expInstance->propertyName);
        $this->assertFalse($expInstance->isStatic);

        // Test with isStatic = true
        $expStatic = new PropertyAccessExpression($targetMock, $propertyNameMock, true);
        $this->assertSame($targetMock, $expStatic->target);
        $this->assertSame($propertyNameMock, $expStatic->propertyName);
        $this->assertTrue($expStatic->isStatic);
    }

    public function testAccept(): void
    {
        /** @var ExpressionInterface&MockObject $targetMock */
        $targetMock = $this->createMock(ExpressionInterface::class);

        /** @var ExpressionInterface&MockObject $propertyNameMock */
        $propertyNameMock = $this->createMock(ExpressionInterface::class);

        $exp = new PropertyAccessExpression($targetMock, $propertyNameMock, false);

        /** @var MockObject&VisitorInterface $visitorMock */
        $visitorMock = $this->createMock(VisitorInterface::class);
        $visitorMock->expects($this->once())
            ->method('visitPropertyAccess')
            ->with($this->identicalTo($exp))
        ;

        $exp->accept($visitorMock);
    }
}
