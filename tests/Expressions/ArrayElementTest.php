<?php

declare(strict_types=1);

namespace Manychois\PevalTests\Expressions;

use Manychois\Peval\Expressions\ArrayElement;
use Manychois\Peval\Expressions\ExpressionInterface;
use Manychois\PevalTests\AbstractBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 *
 * @coversNothing
 */
class ArrayElementTest extends AbstractBaseTestCase
{
    public function testConstructorAndPropertiesWithValueOnly(): void
    {
        /** @var ExpressionInterface&MockObject $valueMock */
        $valueMock = $this->createMock(ExpressionInterface::class);

        $element = new ArrayElement($valueMock);
        $this->assertSame($valueMock, $element->value);
        $this->assertNull($element->key);
    }

    public function testConstructorAndPropertiesWithValueAndKey(): void
    {
        /** @var ExpressionInterface&MockObject $valueMock */
        $valueMock = $this->createMock(ExpressionInterface::class);

        /** @var ExpressionInterface&MockObject $keyMock */
        $keyMock = $this->createMock(ExpressionInterface::class);

        $element = new ArrayElement($valueMock, $keyMock);
        $this->assertSame($valueMock, $element->value);
        $this->assertSame($keyMock, $element->key);
    }
}
