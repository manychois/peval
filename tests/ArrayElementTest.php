<?php

namespace Manychois\PevalTests;

use Manychois\Peval\Expressions\ArrayElement;
use Manychois\Peval\Expressions\ExpressionInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ArrayElementTest extends BaseTestCase
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
